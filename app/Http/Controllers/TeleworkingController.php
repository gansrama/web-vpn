<?php

namespace App\Http\Controllers;

use App\Models\TeleworkingRequest;
use App\Models\Employee;
use App\Utils\MaskingHelper;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class TeleworkingController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Convert string boolean values to actual booleans
        $request->merge([
            'sudah_menandatangani_surat_pernyataan' => filter_var($request->sudah_menandatangani_surat_pernyataan, FILTER_VALIDATE_BOOLEAN),
            'memahami_kebijakan_keamanan' => filter_var($request->memahami_kebijakan_keamanan, FILTER_VALIDATE_BOOLEAN),
        ]);
        
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'nomor_ktp' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'posisi_jabatan' => 'required|string|max:255',
            'nama_organisasi' => 'required|string|max:255',
            'nomer_hp_wa' => 'required|string|max:15',
            'masa_berlaku' => 'required|string|in:q1,q2,q3,q4',
            'keperluan_vpn' => 'required|string',
            'pengguna_hak_akses' => 'required|string|in:asn,non-asn',
            'sudah_menandatangani_surat_pernyataan' => 'required|boolean',
            'memahami_kebijakan_keamanan' => 'required|boolean',
            'signature_image' => 'nullable|file|mimes:png|max:5120', // Max 5MB
            'posisi_pemohon' => 'nullable|string|max:255', // Tambah posisi pemohon
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find existing employee or create new one (prevents duplicates)
            \Log::info('Finding/creating employee with KTP: ' . $request->nomor_ktp);
            $employee = Employee::firstOrCreate(
                ['nomor_ktp' => $request->nomor_ktp],
                [
                    'nama_lengkap' => $request->nama_lengkap,
                    'email' => $request->email,
                    'posisi_jabatan' => $request->posisi_jabatan,
                    'nama_organisasi' => $request->nama_organisasi,
                    'nomer_hp_wa' => $request->nomer_hp_wa,
                ]
            );
            \Log::info('Employee found/created with ID: ' . $employee->id);

            // Handle signature upload
            $signaturePath = null;
            if ($request->hasFile('signature_image')) {
                try {
                    $signatureFile = $request->file('signature_image');
                    $signatureName = time() . '_' . preg_replace('/[^a-zA-Z0-9]/', '_', $employee->email) . '.png';
                    
                    // Ensure directory exists
                    $signatureDir = public_path('storage/signatures');
                    if (!file_exists($signatureDir)) {
                        mkdir($signatureDir, 0755, true);
                    }
                    
                    $signatureFile->move($signatureDir, $signatureName);
                    $signaturePath = 'storage/signatures/' . $signatureName;
                    \Log::info('Signature uploaded to: ' . $signaturePath);
                } catch (\Exception $e) {
                    \Log::error('Signature upload failed: ' . $e->getMessage());
                    // Continue without signature if upload fails
                    $signaturePath = null;
                }
            }

            // Prepare teleworking request data
            $teleworkingData = [
                'employee_id' => $employee->id,
                'masa_berlaku' => $request->masa_berlaku,
                'keperluan_vpn' => $request->keperluan_vpn,
                'pengguna_hak_akses' => $request->pengguna_hak_akses,
                'sudah_menandatangani_surat_pernyataan' => $request->boolean('sudah_menandatangani_surat_pernyataan'),
                'memahami_kebijakan_keamanan' => $request->boolean('memahami_kebijakan_keamanan'),
                'status' => 'pending',
                'request_type' => 'teleworking',
                'posisi_pemohon' => $request->posisi_pemohon, // Tambah posisi pemohon
            ];
            
            // Only add signature_path if it exists
            if ($signaturePath) {
                $teleworkingData['signature_path'] = $signaturePath;
            }
            
            \Log::info('Creating teleworking request with data: ', $teleworkingData);

            // Create Teleworking request
            $teleworkingRequest = TeleworkingRequest::create($teleworkingData);
            \Log::info('Teleworking request created with ID: ' . $teleworkingRequest->id);

            // Create notification for form submission
            NotificationController::createFormSubmissionNotificationStatic(
                'teleworking',
                $employee->email,
                $teleworkingRequest->id,
                'disubmit'
            );

            return response()->json([
                'success' => true,
                'message' => 'Teleworking request submitted successfully',
                'data' => $teleworkingRequest->load('employee')
            ], 201);

        } catch (\Illuminate\Database\QueryException $e) {
            // Handle database specific errors
            return response()->json([
                'success' => false,
                'message' => 'Database error: ' . $e->getMessage(),
                'error_code' => $e->getCode(),
                'sql_state' => $e->errorInfo[0] ?? null
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit Teleworking request: ' . $e->getMessage(),
                'error_type' => get_class($e)
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $teleworkingRequests = TeleworkingRequest::with('employee')
            ->when($request->status, function ($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->search, function ($query, $search) {
                return $query->whereHas('employee', function ($q) use ($search) {
                    $q->where('nama_lengkap', 'like', "%{$search}%")
                      ->orWhere('nomor_ktp', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'desc')
            ->get();

        // Mask employee data in the response
        $maskedRequests = $teleworkingRequests->map(function ($request) {
            if ($request->employee) {
                $request->employee = (object) MaskingHelper::maskEmployeeData($request->employee);
            }
            return $request;
        });

        return response()->json([
            'success' => true,
            'data' => $maskedRequests
        ]);
    }

    public function show($id): JsonResponse
    {
        try {
            $teleworkingRequest = TeleworkingRequest::with('employee')->findOrFail($id);
            
            // Mask employee data in the response
            if ($teleworkingRequest->employee) {
                $teleworkingRequest->employee = (object) MaskingHelper::maskEmployeeData($teleworkingRequest->employee);
            }
            
            return response()->json([
                'success' => true,
                'data' => $teleworkingRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Teleworking request: ' . $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'masa_berlaku' => 'required|string|in:q1,q2,q3,q4',
            'keperluan_vpn' => 'required|string',
            'pengguna_hak_akses' => 'required|string|in:asn,non-asn',
            'sudah_menandatangani_surat_pernyataan' => 'required|boolean',
            'memahami_kebijakan_keamanan' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find existing employee
            $employee = Employee::find($request->employee_id);
            if (!$employee) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee not found'
                ], 404);
            }

            // Update teleworking request
            $teleworkingRequest = TeleworkingRequest::findOrFail($id);
            $teleworkingRequest->update([
                'employee_id' => $request->employee_id,
                'masa_berlaku' => $request->masa_berlaku,
                'keperluan_vpn' => $request->keperluan_vpn,
                'pengguna_hak_akses' => $request->pengguna_hak_akses,
                'sudah_menandatangani_surat_pernyataan' => $request->boolean('sudah_menandatangani_surat_pernyataan'),
                'memahami_kebijakan_keamanan' => $request->boolean('memahami_kebijakan_keamanan'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Teleworking request updated successfully',
                'data' => $teleworkingRequest->load('employee')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Teleworking request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $teleworkingRequest = TeleworkingRequest::findOrFail($id);
            $teleworkingRequest->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Teleworking request status updated successfully',
                'data' => $teleworkingRequest->load('employee')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Teleworking request status: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $total = TeleworkingRequest::count();
            $pending = TeleworkingRequest::where('status', 'pending')->count();
            $approved = TeleworkingRequest::where('status', 'approved')->count();
            $rejected = TeleworkingRequest::where('status', 'rejected')->count();
            $today = TeleworkingRequest::whereDate('created_at', now()->toDateString())->count();
            
            $byStatus = TeleworkingRequest::selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status')
                ->toArray();

            return response()->json([
                'success' => true,
                'data' => [
                    'total' => $total,
                    'pending' => $pending,
                    'approved' => $approved,
                    'rejected' => $rejected,
                    'today' => $today,
                    'byStatus' => $byStatus
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching teleworking statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $teleworkingRequest = TeleworkingRequest::findOrFail($id);
            $teleworkingRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Teleworking request deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Teleworking request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getSignature($id): JsonResponse
    {
        try {
            $teleworkingRequest = TeleworkingRequest::findOrFail($id);
            
            if (!$teleworkingRequest->signature_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'No signature found for this request'
                ], 404);
            }

            $signaturePath = public_path($teleworkingRequest->signature_path);
            
            if (!file_exists($signaturePath)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Signature file not found'
                ], 404);
            }

            $signatureData = file_get_contents($signaturePath);
            $base64Signature = base64_encode($signatureData);

            return response()->json([
                'success' => true,
                'data' => [
                    'signature_base64' => $base64Signature,
                    'signature_path' => $teleworkingRequest->signature_path,
                    'mime_type' => 'image/png'
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve signature: ' . $e->getMessage()
            ], 500);
        }
    }
}
