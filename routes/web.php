<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

// Direct signature image serving route
Route::get('/storage/signatures/{filename}', function($filename) {
    $path = public_path('storage/signatures/' . $filename);
    
    if (!file_exists($path)) {
        abort(404, 'Signature file not found');
    }
    
    return response()->file($path);
})->where('filename', '.*');

// Employee Form Status Route
Route::get('/api/employee-form-status', function (Request $request) {
    try {
        // Get all akses logic requests with employee data
        $aksesLogicRequests = \App\Models\AksesLogicRequest::with('employee')
            ->whereIn('status', ['pending', 'approved'])
            ->get();
        
        // Get all teleworking requests with employee data
        $teleworkingRequests = \App\Models\TeleworkingRequest::with('employee')
            ->whereIn('status', ['pending', 'approved'])
            ->get();
        
        // Map text descriptions to period codes
        $periodMap = [
            'Januari s.d Maret' => 'q1',
            'April s.d Juni' => 'q2',
            'Juli s.d September' => 'q3',
            'Oktober s.d Desember' => 'q4',
        ];
        
        // Build a map of employee_id -> form submissions
        $employeeSubmissions = [];
        
        foreach ($aksesLogicRequests as $request) {
            $employeeId = $request->employee_id;
            if (!isset($employeeSubmissions[$employeeId])) {
                $employeeSubmissions[$employeeId] = [
                    'id' => $request->employee->id,
                    'nama_lengkap' => $request->employee->nama_lengkap,
                    'username_vpn' => $request->employee->username_vpn,
                    'form_submissions' => []
                ];
            }
            
            // Map text period to code
            $periodCode = $periodMap[$request->masa_berlaku] ?? $request->masa_berlaku;
            
            $employeeSubmissions[$employeeId]['form_submissions'][] = [
                'form_type' => 'akses_logic',
                'period' => $periodCode,
                'status' => 'submitted'
            ];
        }
        
        foreach ($teleworkingRequests as $request) {
            $employeeId = $request->employee_id;
            if (!isset($employeeSubmissions[$employeeId])) {
                $employeeSubmissions[$employeeId] = [
                    'id' => $request->employee->id,
                    'nama_lengkap' => $request->employee->nama_lengkap,
                    'username_vpn' => $request->employee->username_vpn,
                    'form_submissions' => []
                ];
            }
            
            // Map text period to code
            $periodCode = $periodMap[$request->masa_berlaku] ?? $request->masa_berlaku;
            
            $employeeSubmissions[$employeeId]['form_submissions'][] = [
                'form_type' => 'teleworking',
                'period' => $periodCode,
                'status' => 'submitted'
            ];
        }
        
        // Convert to array
        $result = array_values($employeeSubmissions);

        return response()->json([
            'success' => true,
            'data' => $result
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching form status: ' . $e->getMessage()
        ], 500);
    }
});

// Debug endpoint to inspect database data
Route::get('/api/debug-form-status', function (Request $request) {
    try {
        $aksesLogicRequests = \App\Models\AksesLogicRequest::with('employee')
            ->whereIn('status', ['pending', 'approved'])
            ->get();
        
        $teleworkingRequests = \App\Models\TeleworkingRequest::with('employee')
            ->whereIn('status', ['pending', 'approved'])
            ->get();
        
        return response()->json([
            'success' => true,
            'akses_logic_requests' => $aksesLogicRequests,
            'teleworking_requests' => $teleworkingRequests,
            'akses_logic_count' => $aksesLogicRequests->count(),
            'teleworking_count' => $teleworkingRequests->count()
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('{any?}', function() {
    return view('application');
})->where('any', '.*');

// Authentication routes with session support
Route::post('/api/login', function (Request $request) {
    try {
        // Validate input
        $validated = $request->validate([
            "email" => "required|email",
            "password" => "required|string"
        ]);

        // Find user by email with profile
        $user = DB::table("users")->where("email", $validated["email"])->first();

        if (!$user) {
            return response()->json([
                "success" => false,
                "message" => "Invalid credentials"
            ], 401);
        }

        // Verify password
        if (!Hash::check($validated["password"], $user->password)) {
            return response()->json([
                "success" => false,
                "message" => "Invalid credentials"
            ], 401);
        }

        // Get user profile with avatar
        $profile = DB::table("profiles")->where("user_id", $user->id)->first();
        $avatar = $profile ? $profile->avatar : null;

        // Store user info in session
        session([
            "user_id" => $user->id,
            "user_name" => $user->name,
            "user_email" => $user->email,
            "user_avatar" => $avatar,
            "logged_in" => true
        ]);

        return response()->json([
            "success" => true,
            "message" => "Login successful",
            "user" => [
                "id" => $user->id,
                "name" => $user->name,
                "email" => $user->email,
                "avatar" => $avatar
            ],
            "session_id" => session()->getId()
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            "success" => false,
            "message" => "Validation failed",
            "errors" => $e->errors()
        ], 422);
    } catch (Exception $e) {
        return response()->json([
            "success" => false,
            "message" => "Login failed: " . $e->getMessage()
        ], 500);
    }
});

Route::post('/api/logout', function (Request $request) {
    try {
        // Clear session
        session()->flush();
        
        return response()->json([
            "success" => true,
            "message" => "Logout successful"
        ]);

    } catch (Exception $e) {
        return response()->json([
            "success" => false,
            "message" => "Logout failed: " . $e->getMessage()
        ], 500);
    }
});

Route::get('/api/user', function (Request $request) {
    try {
        // Check if user is logged in
        if (!session("logged_in")) {
            return response()->json([
                "success" => false,
                "message" => "Not authenticated"
            ], 401);
        }

        $user = [
            "id" => session("user_id"),
            "name" => session("user_name"),
            "email" => session("user_email")
        ];

        return response()->json([
            "success" => true,
            "user" => $user
        ]);

    } catch (Exception $e) {
        return response()->json([
            "success" => false,
            "message" => "Failed to get user info: " . $e->getMessage()
        ], 500);
    }
});

Route::get('/api/check-auth', function (Request $request) {
    try {
        $isAuthenticated = session("logged_in") ?? false;
        
        return response()->json([
            "success" => true,
            "authenticated" => $isAuthenticated,
            "user" => $isAuthenticated ? [
                "id" => session("user_id"),
                "name" => session("user_name"),
                "email" => session("user_email"),
                "avatar" => session("user_avatar")
            ] : null
        ]);

    } catch (Exception $e) {
        return response()->json([
            "success" => false,
            "message" => "Auth check failed: " . $e->getMessage()
        ], 500);
    }
});

// Test API POST Request
Route::get('/test-api-post', function () {
    $employees = DB::table('employees')->get();
    $vpnServers = DB::table('vpn_servers')->get();
    
    return view('test_api_web', [
        'employees' => $employees,
        'vpnServers' => $vpnServers
    ]);
});
