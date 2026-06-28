<?php

namespace App\Http\Controllers;

use App\Models\AksesLogicRequest;
use App\Models\Employee;
use App\Models\VpnServer;
use App\Utils\MaskingHelper;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AksesLogicController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Convert string boolean values to actual booleans
        $request->merge([
            'sudah_menandatangani_surat_pernyataan' => filter_var($request->sudah_menandatangani_surat_pernyataan, FILTER_VALIDATE_BOOLEAN),
            'memahami_kebijakan_keamanan' => filter_var($request->memahami_kebijakan_keamanan, FILTER_VALIDATE_BOOLEAN),
        ]);
        
        // Parse VPN servers from JSON if needed before validation
        $vpnServers = $request->vpn_servers;
        if (is_string($vpnServers)) {
            $vpnServers = json_decode($vpnServers, true);
            // Replace the request value with the parsed array
            $request->merge(['vpn_servers' => $vpnServers]);
        }
        
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'nomor_ktp' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'posisi_jabatan' => 'required|string|max:255',
            'nama_organisasi' => 'required|string|max:255',
            'nomer_hp_wa' => 'required|string|max:15',
            'vpn_servers' => 'required|array|min:1',
            'vpn_servers.*.nama_sistem' => 'required|string',
            'vpn_servers.*.jenis_akses' => 'required|string|in:clientless,client,ipsec',
            'masa_berlaku' => 'required|string|in:q1,q2,q3,q4',
            'keperluan_vpn' => 'required|string',
            'pengguna_hak_akses' => 'required|string|in:asn,non-asn',
            'sudah_menandatangani_surat_pernyataan' => 'required|boolean',
            'memahami_kebijakan_keamanan' => 'required|boolean',
            'signature_image' => 'nullable|file|mimes:png|max:5120',
            'posisi_pemohon' => 'nullable|string|max:255',
        ]);

        // Custom validation for vpn_servers nama_sistem
        $validator->after(function ($validator) use ($request) {
            try {
                $vpnServers = $request->vpn_servers;
                if (!is_array($vpnServers)) {
                    $vpnServers = json_decode($request->vpn_servers, true);
                }
                
                if (is_array($vpnServers)) {
                    foreach ($vpnServers as $index => $server) {
                        $namaSistem = trim($server['nama_sistem'] ?? '');
                        
                        // Allow special cases
                        $allowedSpecialCases = ['All Product', 'All IP Product'];
                        if (in_array($namaSistem, $allowedSpecialCases)) {
                            continue; // Skip validation for special cases
                        }
                        
                        // Check if nama_sistem exists in database for regular cases
                        $exists = \App\Models\VpnServer::where('nama_sistem', $namaSistem)->exists();
                        
                        if (!$exists) {
                            $validator->errors()->add("vpn_servers.{$index}.nama_sistem", "The selected nama sistem '{$namaSistem}' is invalid.");
                        }
                    }
                }
            } catch (\Exception $e) {
                \Log::error('Error in custom validation: ' . $e->getMessage());
                $validator->errors()->add('vpn_servers', 'Validation error for VPN servers.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Find existing employee or create new one (prevents duplicates)
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

            // Prepare akses logic request data (without single server fields)
            $aksesLogicData = [
                'employee_id' => $employee->id,
                'nama_sistem' => 'Multiple Systems', // Placeholder for backward compatibility
                'ip_address' => 'Multiple IPs', // Placeholder for backward compatibility
                'jenis_akses' => $vpnServers[0]['jenis_akses'] ?? 'clientless', // Use first server's access type
                'masa_berlaku' => $request->masa_berlaku,
                'keperluan_vpn' => $request->keperluan_vpn,
                'pengguna_hak_akses' => $request->pengguna_hak_akses,
                'sudah_menandatangani_surat_pernyataan' => $request->boolean('sudah_menandatangani_surat_pernyataan'),
                'memahami_kebijakan_keamanan' => $request->boolean('memahami_kebijakan_keamanan'),
                'status' => 'pending',
                'request_type' => 'akses_logic',
                'posisi_pemohon' => $request->posisi_pemohon,
            ];

            // Only add signature_path if it exists
            if ($signaturePath) {
                $aksesLogicData['signature_path'] = $signaturePath;
            }

            // Create Akses Logic request
            $aksesLogicRequest = AksesLogicRequest::create($aksesLogicData);

            // Create Akses Logic Items for each VPN server
            foreach ($vpnServers as $serverData) {
                $namaSistem = trim($serverData['nama_sistem']);
                \App\Models\AksesLogicItem::create([
                    'akses_logic_request_id' => $aksesLogicRequest->id,
                    'nama_sistem' => $namaSistem,
                    'ip_address' => $this->getIpAddressForServer($namaSistem),
                    'jenis_akses' => $serverData['jenis_akses'],
                ]);
            }

            // Create notification for form submission
            NotificationController::createFormSubmissionNotificationStatic(
                'akses-logic',
                $employee->email,
                $aksesLogicRequest->id,
                'disubmit'
            );

            return response()->json([
                'success' => true,
                'message' => 'Akses Logic request submitted successfully',
                'data' => $aksesLogicRequest->load(['employee', 'aksesLogicItems'])
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit Akses Logic request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $aksesLogicRequests = AksesLogicRequest::with(['employee', 'aksesLogicItems'])
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
        $maskedRequests = $aksesLogicRequests->map(function ($request) {
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
            $aksesLogicRequest = AksesLogicRequest::with(['employee', 'aksesLogicItems'])->findOrFail($id);
            
            // Mask employee data in the response
            if ($aksesLogicRequest->employee) {
                $aksesLogicRequest->employee = (object) MaskingHelper::maskEmployeeData($aksesLogicRequest->employee);
            }
            
            return response()->json([
                'success' => true,
                'data' => $aksesLogicRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch Akses Logic request: ' . $e->getMessage()
            ], 404);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'employee_id' => 'required|integer',
            'nama_sistem' => 'required|string|exists:vpn_servers,nama_sistem',
            'jenis_akses' => 'required|string|in:clientless,client,ipsec',
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

            // Update akses logic request
            $aksesLogicRequest = AksesLogicRequest::findOrFail($id);
            $aksesLogicRequest->update([
                'employee_id' => $request->employee_id,
                'nama_sistem' => $request->nama_sistem,
                'ip_address' => $this->getIpAddressForServer($request->nama_sistem),
                'jenis_akses' => $request->jenis_akses,
                'masa_berlaku' => $request->masa_berlaku,
                'keperluan_vpn' => $request->keperluan_vpn,
                'pengguna_hak_akses' => $request->pengguna_hak_akses,
                'sudah_menandatangani_surat_pernyataan' => $request->boolean('sudah_menandatangani_surat_pernyataan'),
                'memahami_kebijakan_keamanan' => $request->boolean('memahami_kebijakan_keamanan'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Akses Logic request updated successfully',
                'data' => $aksesLogicRequest->load('employee')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Akses Logic request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $aksesLogicRequest = AksesLogicRequest::findOrFail($id);
            $aksesLogicRequest->delete();

            return response()->json([
                'success' => true,
                'message' => 'Akses Logic request deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete Akses Logic request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function stats(): JsonResponse
    {
        try {
            $total = AksesLogicRequest::count();
            $pending = AksesLogicRequest::where('status', 'pending')->count();
            $approved = AksesLogicRequest::where('status', 'approved')->count();
            $rejected = AksesLogicRequest::where('status', 'rejected')->count();
            $today = AksesLogicRequest::whereDate('created_at', now()->format('Y-m-d'))->count();
            
            $byStatus = AksesLogicRequest::selectRaw('status, COUNT(*) as count')
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
                'message' => 'Failed to fetch Akses Logic request statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|string|in:pending,approved,rejected',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $aksesLogicRequest = AksesLogicRequest::findOrFail($id);
            $aksesLogicRequest->update([
                'status' => $request->status,
                'updated_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Akses Logic request status updated successfully',
                'data' => $aksesLogicRequest
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update Akses Logic request status: ' . $e->getMessage()
            ], 500);
        }
    }

    private function getIpAddressForServer(string $serverName): string
    {
        // Handle special cases
        if ($serverName === 'All Product' || $serverName === 'All IP Product') {
            return 'All IP Product';
        }
        
        // Look up regular servers in database
        $server = VpnServer::where('nama_sistem', $serverName)->first();
        return $server ? $server->ip_address : '192.168.1.100';
    }

    public function getSignature($id): JsonResponse
    {
        try {
            $aksesLogicRequest = AksesLogicRequest::findOrFail($id);
            
            if (!$aksesLogicRequest->signature_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'No signature found for this request'
                ], 404);
            }

            $signaturePath = public_path($aksesLogicRequest->signature_path);
            
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
                    'signature_path' => $aksesLogicRequest->signature_path,
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
