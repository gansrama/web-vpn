# API Signature Documentation

## Overview
API untuk mengambil data signature PNG dan posisi pemohon dari setiap request form (Teleworking dan Akses Logic).

## Database Schema Update
Kolom `posisi_pemohon` telah ditambahkan ke kedua tabel:
- `teleworking_requests.posisi_pemohon` (string, nullable)
- `akses_logic_requests.posisi_pemohon` (string, nullable)

## Endpoints

### 1. Get Teleworking Request Signature
**GET** `/api/teleworking-requests/{id}/signature`

Mengambil data signature (base64) dari request teleworking tertentu.

**Parameters:**
- `id` (integer) - ID dari teleworking request

**Response:**
```json
{
  "success": true,
  "data": {
    "signature_base64": "iVBORw0KGgoAAAANSUhEUgAA...",
    "signature_path": "storage/signatures/1648xxxx_test_email.png",
    "mime_type": "image/png"
  }
}
```

### 2. Get Akses Logic Request Signature
**GET** `/api/akses-logic-requests/{id}/signature`

Mengambil data signature (base64) dari request akses logic tertentu.

**Parameters:**
- `id` (integer) - ID dari akses logic request

**Response:**
```json
{
  "success": true,
  "data": {
    "signature_base64": "iVBORw0KGgoAAAANSUhEUgAA...",
    "signature_path": "storage/signatures/1648xxxx_test_email.png",
    "mime_type": "image/png"
  }
}
```

## Form Submission Fields

### Teleworking Request Form
**POST** `/api/teleworking-requests`

Field baru yang ditambahkan:
- `posisi_pemohon` (string, optional, max 255) - Posisi/letak pemohon

### Akses Logic Request Form  
**POST** `/api/akses-logic-requests`

Field baru yang ditambahkan:
- `posisi_pemohon` (string, optional, max 255) - Posisi/letak pemohon

## Usage Examples

### JavaScript/Frontend
```javascript
// Get teleworking signature
async function getTeleworkingSignature(requestId) {
  try {
    const response = await fetch(`/api/teleworking-requests/${requestId}/signature`);
    const result = await response.json();
    
    if (result.success) {
      // Create image from base64
      const imgSrc = `data:${result.data.mime_type};base64,${result.data.signature_base64}`;
      document.getElementById('signature-img').src = imgSrc;
    } else {
      console.error('Signature not found:', result.message);
    }
  } catch (error) {
    console.error('Error fetching signature:', error);
  }
}

// Submit form with posisi_pemohon
const formData = {
  // ... other fields
  posisi_pemohon: "Jakarta, 30 Maret 2026", // Contoh posisi pemohon
  signature_image: signatureFile // PNG file
};
```

### PHP/Backend
```php
// Get teleworking signature
$client = new GuzzleHttp\Client();
$response = $client->get("/api/teleworking-requests/{$requestId}/signature");
$data = json_decode($response->getBody(), true);

if ($data['success']) {
    $signatureBase64 = $data['data']['signature_base64'];
    $imageData = base64_decode($signatureBase64);
    // Save or display the image
}
```

## Data Structure

### TeleworkingRequest Model
```php
protected $fillable = [
    'employee_id',
    'masa_berlaku',
    'keperluan_vpn',
    'pengguna_hak_akses',
    'sudah_menandatangani_surat_pernyataan',
    'memahami_kebijakan_keamanan',
    'status',
    'catatan',
    'request_type',
    'signature_path', // Path ke file signature
    'posisi_pemohon', // Posisi/letak pemohon
];
```

### AksesLogicRequest Model
```php
protected $fillable = [
    'employee_id',
    'nama_sistem',
    'ip_address',
    'jenis_akses',
    'masa_berlaku',
    'keperluan_vpn',
    'pengguna_hak_akses',
    'sudah_menandatangani_surat_pernyataan',
    'memahami_kebijakan_keamanan',
    'status',
    'catatan',
    'request_type',
    'signature_path', // Path ke file signature
    'posisi_pemohon', // Posisi/letak pemohon
];
```

## File Storage
- Signature files disimpan di `public/storage/signatures/`
- Format file: PNG
- Max file size: 5MB
- Naming format: `{timestamp}_{sanitized_email}.png`

## Notes
- Signature bersifat optional (nullable)
- `posisi_pemohon` bersifat optional (nullable)
- Jika signature tidak ditemukan, API akan return 404
- Base64 encoding digunakan untuk memudahkan transfer data
- Pastikan directory `public/storage/signatures/` memiliki permission yang tepat
