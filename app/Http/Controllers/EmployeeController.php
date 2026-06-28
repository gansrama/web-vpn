<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Utils\MaskingHelper;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class EmployeeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Employee::query();
        
        // Search functionality
        if ($request->search) {
            $query->where('nama_lengkap', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%")
                  ->orWhere('username_vpn', 'like', "%{$request->search}%")
                  ->orWhere('posisi_jabatan', 'like', "%{$request->search}%")
                  ->orWhere('nama_organisasi', 'like', "%{$request->search}%")
                  ->orWhere('nomor_ktp', 'like', "%{$request->search}%");
        }
        
        $employees = $query->orderBy('created_at', 'desc')
                         ->paginate($request->per_page ?? 10);

        // Mask sensitive data in the response
        $maskedEmployees = collect($employees->items())->map(function ($employee) {
            return MaskingHelper::maskEmployeeData($employee);
        })->toArray();

        return response()->json([
            'success' => true,
            'data' => $maskedEmployees,
            'pagination' => [
                'current_page' => $employees->currentPage(),
                'last_page' => $employees->lastPage(),
                'per_page' => $employees->perPage(),
                'total' => $employees->total(),
            ]
        ]);
    }

    public function show(Employee $employee): JsonResponse
    {
        // Mask sensitive data in the response
        $maskedEmployee = MaskingHelper::maskEmployeeData($employee);

        return response()->json([
            'success' => true,
            'data' => $maskedEmployee
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nomor_ktp' => 'required|string|unique:employees,nomor_ktp|max:16',
            'email' => 'required|email|max:255',
            'username_vpn' => 'required|string|max:255',
            'posisi_jabatan' => 'required|string|max:255',
            'nama_organisasi' => 'required|string|max:255',
            'nomer_hp_wa' => 'required|string|max:15',
        ]);

        $employee = Employee::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Employee created successfully',
            'data' => $employee
        ], 201);
    }

    public function update(Request $request, Employee $employee): JsonResponse
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'nomor_ktp' => 'required|string|unique:employees,nomor_ktp,' . $employee->id . '|max:16',
            'email' => 'required|email|max:255',
            'username_vpn' => 'required|string|max:255',
            'posisi_jabatan' => 'required|string|max:255',
            'nama_organisasi' => 'required|string|max:255',
            'nomer_hp_wa' => 'required|string|max:15',
        ]);

        $employee->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Employee updated successfully',
            'data' => $employee
        ]);
    }

    public function destroy(Employee $employee): JsonResponse
    {
        try {
            // For now, allow deletion without checking related requests
            // TODO: Add back relationship checks once models are properly configured
            $employee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete employee: ' . $e->getMessage()
            ], 500);
        }
    }

    public function showUnmasked(Employee $employee): JsonResponse
    {
        // Return unmasked data for form submission
        return response()->json([
            'success' => true,
            'data' => $employee
        ]);
    }

    public function statistics(): JsonResponse
    {
        $stats = [
            'total_employees' => Employee::count(),
            'by_position' => Employee::select('posisi_jabatan', Employee::raw('count(*) as count'))
                            ->groupBy('posisi_jabatan')
                            ->orderBy('count', 'desc')
                            ->get(),
            'by_organization' => Employee::select('nama_organisasi', Employee::raw('count(*) as count'))
                                ->groupBy('nama_organisasi')
                                ->orderBy('count', 'desc')
                                ->get(),
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function stats(): JsonResponse
    {
        $stats = [
            'total' => Employee::count(),
            'active' => Employee::count() // Assuming all employees are active for now
        ];

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    public function getFormStatus(): JsonResponse
    {
        try {
            $employees = Employee::with(['aksesLogicRequests', 'teleworkingRequests', 'vpnRequests'])->get();
            
            $periods = ['q1', 'q2', 'q3', 'q4'];
            
            $result = $employees->map(function ($employee) use ($periods) {
                $formSubmissions = [];
                
                // Check Akses Logic submissions
                foreach ($periods as $period) {
                    $hasSubmission = $employee->aksesLogicRequests
                        ->where('masa_berlaku', $period)
                        ->where('status', 'approved')
                        ->isNotEmpty();
                    
                    if ($hasSubmission) {
                        $formSubmissions[] = [
                            'form_type' => 'akses_logic',
                            'period' => $period,
                            'status' => 'approved'
                        ];
                    }
                }
                
                // Check Teleworking submissions
                foreach ($periods as $period) {
                    $hasSubmission = $employee->teleworkingRequests
                        ->where('masa_berlaku', $period)
                        ->where('status', 'approved')
                        ->isNotEmpty();
                    
                    if ($hasSubmission) {
                        $formSubmissions[] = [
                            'form_type' => 'teleworking',
                            'period' => $period,
                            'status' => 'approved'
                        ];
                    }
                }
                
                // Check Google Form submissions (using vpn_requests as proxy for Google Form)
                foreach ($periods as $period) {
                    $hasSubmission = $employee->vpnRequests
                        ->where('masa_berlaku', $period)
                        ->where('status', 'approved')
                        ->isNotEmpty();
                    
                    if ($hasSubmission) {
                        $formSubmissions[] = [
                            'form_type' => 'google_form',
                            'period' => $period,
                            'status' => 'approved'
                        ];
                    }
                }
                
                return [
                    'id' => $employee->id,
                    'nama_lengkap' => $employee->nama_lengkap,
                    'username_vpn' => $employee->username_vpn,
                    'form_submissions' => $formSubmissions
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching form status: ' . $e->getMessage()
            ], 500);
        }
    }
}
