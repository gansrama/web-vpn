import cv2
import numpy as np
import os
import shutil
from fastapi import FastAPI, File, UploadFile, HTTPException
from fastapi.responses import JSONResponse
from fastapi.middleware.cors import CORSMiddleware

# --- SILENT TENSORFLOW WARNINGS ---
os.environ['TF_CPP_MIN_LOG_LEVEL'] = '2'
os.environ['TF_ENABLE_ONEDNN_OPTS'] = '0'

# --- CONDITIONAL DEEPFACE IMPORT ---
try:
    from deepface import DeepFace  # type: ignore
    DEEPFACE_AVAILABLE = True
except ImportError as e:
    print(f"[WARNING] DeepFace tidak tersedia: {str(e)}")
    print("[INFO] Server akan berjalan dalam mode demo")
    DEEPFACE_AVAILABLE = False
    DeepFace = None  # type: ignore

app = FastAPI(
    title="KYC Face Verification API",
    description="API untuk memverifikasi wajah pada KTP dengan video selfie",
    version="1.0"
)

# --- CORS Configuration untuk akses dari domain custom ---
app.add_middleware(
    CORSMiddleware,
    allow_origins=[
        "http://verifikasinik.ai",
        "http://verifikasinik.ai:8000",
        "http://verifikasinik.ai:8080",
        "http://localhost",
        "http://localhost:8000",
        "http://127.0.0.1",
        "http://127.0.0.1:8000",
        "*"  # Allow all origins for development
    ],
    allow_credentials=True,
    allow_methods=["*"],
    allow_headers=["*"],
)

MODEL_NAME = "VGG-Face"

# Inisialisasi Detektor Wajah OpenCV (untuk validasi frame video)
face_cascade = cv2.CascadeClassifier(cv2.data.haarcascades + 'haarcascade_frontalface_default.xml')

def verify_faces(ktp_path, frame):
    """Logika verifikasi DeepFace yang disesuaikan untuk backend"""
    if not DEEPFACE_AVAILABLE:
        return {"verified": False, "status": "DeepFace tidak tersedia - Demo Mode", "distance": 0.5, "threshold": 0.40}
    
    try:
        result = DeepFace.verify(
            img1_path=ktp_path, 
            img2_path=frame, 
            model_name=MODEL_NAME,
            detector_backend="mtcnn",
            enforce_detection=False 
        )
        return {
            "verified": bool(result['verified']),
            "distance": float(result['distance']),
            "threshold": float(result['threshold']),
            "status": "Cocok" if result['verified'] else "Tidak Cocok"
        }
    except Exception as e:
        return {"verified": False, "status": f"Error verifikasi: {str(e)}", "distance": 1.0, "threshold": 0.0}

@app.get("/")
async def root():
    """Root endpoint menampilkan informasi server"""
    return {
        "message": "KYC Face Verification API",
        "status": "running",
        "deepface_available": DEEPFACE_AVAILABLE,
        "mode": "production" if DEEPFACE_AVAILABLE else "demo",
        "docs": "/docs",
        "api_endpoint": "/api/verify-kyc",
        "health": "/health"
    }

@app.get("/health")
async def health():
    """Health check endpoint"""
    return {
        "status": "ok",
        "message": "Server is running",
        "deepface_available": DEEPFACE_AVAILABLE
    }

@app.post("/api/verify-kyc")
async def verify_kyc(
    foto_ktp: UploadFile = File(..., description="File gambar KTP (jpg/png)"),
    video_selfie: UploadFile = File(..., description="File video selfie (mp4/avi/mov)")
):
    # Buat direktori temporary untuk menyimpan file upload sementara
    temp_dir = "temp_uploads"
    os.makedirs(temp_dir, exist_ok=True)
    
    ktp_path = os.path.join(temp_dir, f"temp_{foto_ktp.filename}")
    video_path = os.path.join(temp_dir, f"temp_{video_selfie.filename}")
    
    try:
        # 1. Simpan file upload ke penyimpanan lokal sementara
        with open(ktp_path, "wb") as buffer:
            shutil.copyfileobj(foto_ktp.file, buffer)
            
        with open(video_path, "wb") as buffer:
            shutil.copyfileobj(video_selfie.file, buffer)
            
        # 2. Validasi Gambar KTP Menggunakan OpenCV
        ktp_img = cv2.imread(ktp_path)
        if ktp_img is None:
            raise HTTPException(status_code=400, detail="File KTP rusak atau format tidak didukung.")
            
        # 3. Proses Video Selfie Menggunakan OpenCV
        cap = cv2.VideoCapture(video_path)
        if not cap.isOpened():
            raise HTTPException(status_code=400, detail="File video selfie rusak atau format tidak didukung.")
            
        frames_to_check = []
        frame_counter = 0
        
        # Ekstrak frame dari video secara berkala (mirip logika % 30 Anda)
        while True:
            ret, frame = cap.read()
            if not ret:
                break
                
            frame_counter += 1
            # Ambil frame setiap 15 frame agar deteksi di video pendek lebih akurat
            if frame_counter % 15 == 0:
                gray_frame = cv2.cvtColor(frame, cv2.COLOR_BGR2GRAY)
                faces = face_cascade.detectMultiScale(gray_frame, scaleFactor=1.1, minNeighbors=5, minSize=(100, 100))
                
                # Jika di frame ini OpenCV mendeteksi ada wajah, simpan untuk di-verify DeepFace
                if len(faces) > 0:
                    frames_to_check.append(frame.copy())
                    
        cap.release()
        
        # Jika tidak ada wajah sama sekali terdeteksi di video oleh OpenCV
        if not frames_to_check:
            return JSONResponse(status_code=200, content={
                "success": False,
                "message": "Wajah tidak terdeteksi di sepanjang video selfie.",
                "verified": False
            })
            
        # 4. Lakukan Verifikasi Menggunakan DeepFace
        # Kita akan cek frame-frame yang sudah lolos seleksi wajah di atas
        best_match = None
        is_verified = False
        
        for frame in frames_to_check:
            res = verify_faces(ktp_path, frame)
            if res["verified"]:
                is_verified = True
                best_match = res
                break # Jika sudah ketemu yang cocok, langsung stop perulangan
            else:
                if best_match is None or res["distance"] < best_match["distance"]:
                    best_match = res # Simpan skor jarak terdekat meskipun belum cocok
                    
        # 5. Format Response Output
        response_data = {
            "success": True,
            "verified": is_verified,
            "model_used": MODEL_NAME,
            "mode": "production" if DEEPFACE_AVAILABLE else "demo",
            "detail": best_match if best_match else {"status": "Gagal menganalisis wajah", "distance": 1.0},
            "note": "Demo mode - DeepFace tidak tersedia" if not DEEPFACE_AVAILABLE else None
        }
        
        return JSONResponse(status_code=200, content=response_data)
        
    except Exception as e:
        return JSONResponse(status_code=500, content={"success": False, "message": f"Internal Server Error: {str(e)}"})
        
    finally:
        # Bersihkan file temporary agar penyimpanan server tidak penuh
        if os.path.exists(ktp_path):
            os.remove(ktp_path)
        if os.path.exists(video_path):
            os.remove(video_path)

if __name__ == "__main__":
    import uvicorn
    # Menjalankan server di port 8000
    uvicorn.run(app, host="0.0.0.0", port=8000)