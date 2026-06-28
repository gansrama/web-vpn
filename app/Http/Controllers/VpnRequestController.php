<?php

namespace App\Http\Controllers;

use App\Models\VpnRequest;
use App\Models\Employee;
use App\Models\VpnServer;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class VpnRequestController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'nama_lengkap' => 'required|string|max:255',
            'nomor_ktp' => 'required|string|unique:employees,nomor_ktp|max:16',
            'email' => 'required|email|max:255',
            'posisi_jabatan' => 'required|string|max:255',
            'nama_organisasi' => 'required|string|max:255',
            'nomer_hp_wa' => 'required|string|max:15',
            'nama_sistem' => 'required|string|exists:vpn_servers,nama_sistem',
            'jenis_akses' => 'required|string|in:basic,standard,premium,enterprise',
            'masa_berlaku' => 'required|string|in:q1,q2,q3,q4',
            'keperluan_vpn' => 'required|string',
            'pengguna_hak_akses' => 'required|string|in:asn,non-asn',
            'sudah_menandatangani_surat_pernyataan' => 'required|boolean',
            'memahami_kebijakan_keamanan' => 'required|boolean',
            'signature_image' => 'nullable|file|mimes:png|max:5120', // Max 5MB
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create or find employee
            $employee = Employee::updateOrCreate(
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
                $signatureFile = $request->file('signature_image');
                $signatureName = time() . '_' . $employee->email . '.png';
                $signatureFile->move(public_path('storage/signatures/' . $signatureName));
                $signaturePath = 'storage/signatures/' . $signatureName;
            }

            // Create VPN request
            $vpnRequest = VpnRequest::create([
                'employee_id' => $employee->id,
                'nama_sistem' => $request->nama_sistem,
                'ip_address' => $this->getIpAddressForServer($request->nama_sistem),
                'jenis_akses' => $request->jenis_akses,
                'masa_berlaku' => $request->masa_berlaku,
                'keperluan_vpn' => $request->keperluan_vpn,
                'pengguna_hak_akses' => $request->pengguna_hak_akses,
                'sudah_menandatangani_surat_pernyataan' => $request->boolean('sudah_menandatangani_surat_pernyataan'),
                'memahami_kebijakan_keamanan' => $request->boolean('memahami_kebijakan_keamanan'),
                'status' => 'pending',
                'signature_path' => $signaturePath, // Simpan path signature
            ]);

            return response()->json([
                'success' => true,
                'message' => 'VPN request submitted successfully',
                'data' => $vpnRequest->load('employee')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit VPN request: ' . $e->getMessage()
            ], 500);
        }
    }

    public function index(Request $request): JsonResponse
    {
        $vpnRequests = VpnRequest::with('employee')
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
            ->paginate(10);

        return response()->json([
            'success' => true,
            'data' => $vpnRequests
        ]);
    }

    public function updateStatus(Request $request, VpnRequest $vpnRequest): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:approved,rejected',
            'catatan' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        $vpnRequest->update([
            'status' => $request->status,
            'catatan' => $request->catatan,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'VPN request status updated successfully',
            'data' => $vpnRequest->load('employee')
        ]);
    }

    private function getIpAddressForServer(string $serverName): string
    {
        $server = VpnServer::where('nama_sistem', $serverName)->first();
        return $server ? $server->ip_address : '192.168.1.100';
    }
}
