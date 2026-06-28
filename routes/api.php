<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\VpnRequestController;
use App\Http\Controllers\AksesLogicController;
use App\Http\Controllers\TeleworkingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\VpnServerController;
use App\Http\Controllers\NotificationController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('api')->group(function () {
    // Profile Routes (Protected)
    Route::middleware('web')->group(function () {
        Route::get('/profile', [ProfileController::class, 'index']);
        Route::put('/profile', [ProfileController::class, 'update']);
        Route::post('/profile/avatar', [ProfileController::class, 'uploadAvatar']);
    });
    
    // Employee Routes
    Route::get('/employees', [App\Http\Controllers\EmployeeController::class, 'index']);
    Route::post('/employees', [App\Http\Controllers\EmployeeController::class, 'store']);
    Route::get('/employees/statistics', [App\Http\Controllers\EmployeeController::class, 'statistics']);
    Route::get('/employees/stats', [App\Http\Controllers\EmployeeController::class, 'stats']);
    Route::get('/employees/{employee}', [App\Http\Controllers\EmployeeController::class, 'show']);
    Route::put('/employees/{employee}', [App\Http\Controllers\EmployeeController::class, 'update']);
    Route::delete('/employees/{employee}', [App\Http\Controllers\EmployeeController::class, 'destroy']);
    
    // Employee Routes for Form Submission (Unmasked)
    Route::get('/employees/{employee}/unmasked', [App\Http\Controllers\EmployeeController::class, 'showUnmasked']);
    
    // Employee Form Status Route
    Route::get('/employee-form-status', [App\Http\Controllers\EmployeeController::class, 'getFormStatus']);
    
    // Akses Logic Routes
    Route::get('/akses-logic-requests', [AksesLogicController::class, 'index']);
    Route::get('/akses-logic-requests/stats', [AksesLogicController::class, 'stats']);
    Route::get('/akses-logic-requests/{id}', [AksesLogicController::class, 'show']);
    Route::get('/akses-logic-requests/{id}/signature', [AksesLogicController::class, 'getSignature']);
    Route::post('/akses-logic-requests', [AksesLogicController::class, 'store']);
    Route::put('/akses-logic-requests/{id}', [AksesLogicController::class, 'update']);
    Route::put('/akses-logic-requests/{aksesLogicRequest}/status', [AksesLogicController::class, 'updateStatus']);
    Route::delete('/akses-logic-requests/{id}', [AksesLogicController::class, 'destroy']);
    
    // Teleworking Routes
    Route::get('/teleworking-requests/stats', [TeleworkingController::class, 'stats']);
    Route::get('/teleworking-requests/{id}', [TeleworkingController::class, 'show']);
    Route::get('/teleworking-requests/{id}/signature', [TeleworkingController::class, 'getSignature']);
    Route::post('/teleworking-requests', [TeleworkingController::class, 'store']);
    Route::put('/teleworking-requests/{id}/status', [TeleworkingController::class, 'updateStatus']);
    Route::put('/teleworking-requests/{id}', [TeleworkingController::class, 'update']);
    Route::delete('/teleworking-requests/{id}', [TeleworkingController::class, 'destroy']);
    Route::get('/teleworking-requests', [TeleworkingController::class, 'index']);
    
    // VPN Servers Routes
    Route::get('/vpn-servers', [VpnServerController::class, 'index']);
    Route::post('/vpn-servers', [VpnServerController::class, 'store']);
    Route::get('/vpn-servers/{id}', [VpnServerController::class, 'show']);
    Route::put('/vpn-servers/{id}', [VpnServerController::class, 'update']);
    Route::delete('/vpn-servers/{id}', [VpnServerController::class, 'destroy']);
    
    // Notification Routes
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications/unread-count', [NotificationController::class, 'unreadCount']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead']);
    Route::post('/notifications/form-submission', [NotificationController::class, 'addFormSubmissionNotification']);
    Route::get('/notifications/broadcasts', [NotificationController::class, 'getBroadcasts']);
    
    // Recent Requests Route
    Route::get('/recent-requests', function (Request $request) {
        $limit = $request->get('limit', 5);
        
        // Get recent teleworking requests
        $teleworkingRequests = \App\Models\TeleworkingRequest::with('employee')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'request_type' => 'teleworking',
                    'status' => $request->status,
                    'employee' => $request->employee ? ['nama_lengkap' => $request->employee->nama_lengkap] : null,
                    'created_at' => $request->created_at
                ];
            });
        
        // Get recent akses logic requests
        $aksesLogicRequests = \App\Models\AksesLogicRequest::with('employee')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($request) {
                return [
                    'id' => $request->id,
                    'request_type' => 'akses_logic',
                    'status' => $request->status,
                    'employee' => $request->employee ? ['nama_lengkap' => $request->employee->nama_lengkap] : null,
                    'created_at' => $request->created_at
                ];
            });
        
        // Merge and sort by created_at
        $allRequests = $teleworkingRequests->concat($aksesLogicRequests)
            ->sortByDesc('created_at')
            ->take($limit)
            ->values();
        
        return response()->json([
            'success' => true,
            'data' => $allRequests
        ]);
    });
    
    // SSE Route for real-time notifications
    Route::get('/notifications/stream', function (Request $request) {
        // Set headers untuk SSE
        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('Connection: keep-alive');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Headers: Cache-Control');
        
        // Disable output buffering
        if (function_exists('apache_setenv')) {
            apache_setenv('no-gzip', 1);
        }
        ini_set('zlib.output_compression', 0);
        ini_set('output_buffering', 'Off');
        ini_set('implicit_flush', 1);
        
        $lastId = $request->get('last_id', 0);
        $sessionId = session()->getId();
        
        // Check if table exists
        if (!\Schema::hasTable('notification_broadcasts')) {
            // Try to create the table automatically
            try {
                \Schema::create('notification_broadcasts', function (Blueprint $table) {
                    $table->id();
                    $table->foreignId('notification_id')->constrained()->onDelete('cascade');
                    $table->json('payload');
                    $table->timestamp('created_at')->default(now());
                    
                    $table->index('created_at');
                });
                
                Log::info('Notification broadcasts table created successfully');
            } catch (\Exception $e) {
                Log::error('Failed to create notification broadcasts table: ' . $e->getMessage());
                
                echo "event: error\n";
                echo "data: " . json_encode([
                    'type' => 'error',
                    'message' => 'Notification broadcasts table not found and could not be created. Please run database migrations.',
                    'timestamp' => now()->toISOString()
                ]) . "\n\n";
                
                ob_flush();
                flush();
                exit;
            }
        }
        
        // Send initial connection message
        echo "event: connected\n";
        echo "data: " . json_encode([
            'type' => 'connected',
            'session_id' => $sessionId,
            'timestamp' => now()->toISOString()
        ]) . "\n\n";
        
        ob_flush();
        flush();
        
        $counter = 0;
        $maxIterations = 100; // Reduced from 1000 to prevent long-running issues
        
        while ($counter < $maxIterations) {
            try {
                // Check for new broadcasts
                $broadcasts = DB::table('notification_broadcasts')
                    ->where('id', '>', $lastId)
                    ->orderBy('id', 'asc')
                    ->limit(10)
                    ->get();

                if ($broadcasts->isNotEmpty()) {
                    foreach ($broadcasts as $broadcast) {
                        $payload = json_decode($broadcast->payload, true);
                        
                        echo "id: " . $broadcast->id . "\n";
                        echo "event: notification\n";
                        echo "data: " . json_encode([
                            'type' => 'notification',
                            'notification' => $payload,
                            'timestamp' => now()->toISOString()
                        ]) . "\n\n";
                        
                        $lastId = $broadcast->id;
                    }
                    
                    // Send to client immediately
                    ob_flush();
                    flush();
                } else {
                    // Send heartbeat every 30 seconds to keep connection alive
                    if ($counter % 10 === 0) {
                        echo "event: heartbeat\n";
                        echo "data: " . json_encode([
                            'type' => 'heartbeat',
                            'timestamp' => now()->toISOString()
                        ]) . "\n\n";
                        
                        ob_flush();
                        flush();
                    }
                }

                // Clean up old broadcasts periodically
                if ($counter % 20 === 0) {
                    try {
                        DB::table('notification_broadcasts')
                            ->where('created_at', '<', now()->subHour())
                            ->delete();
                    } catch (\Exception $cleanupError) {
                        // Ignore cleanup errors to keep stream alive
                        Log::error('Cleanup error: ' . $cleanupError->getMessage());
                    }
                }

                // Sleep for 3 seconds before next check
                sleep(3);
                $counter++;
                
            } catch (\Exception $e) {
                echo "event: error\n";
                echo "data: " . json_encode([
                    'type' => 'error',
                    'message' => $e->getMessage(),
                    'timestamp' => now()->toISOString()
                ]) . "\n\n";
                
                ob_flush();
                flush();
                break;
            }
        }
        
        // Send disconnect message
        echo "event: disconnected\n";
        echo "data: " . json_encode([
            'type' => 'disconnected',
            'message' => 'Stream ended',
            'timestamp' => now()->toISOString()
        ]) . "\n\n";
        
        ob_flush();
        flush();
        
        exit;
    });
    
    // Admin notification routes (you might want to add middleware for admin access)
    Route::post('/notifications/global', [NotificationController::class, 'createGlobal']);
    
    // Access Logs Routes
    Route::get('/akses-logic-logs', function (Request $request) {
        $logs = \App\Models\AksesLogicLog::with('aksesLogicRequest.employee')
            ->when($request->akses_logic_request_id, function ($query, $aksesLogicRequestId) {
                return $query->where('akses_logic_request_id', $aksesLogicRequestId);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('login_time', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('login_time', '<=', $dateTo);
            })
            ->orderBy('login_time', 'desc')
            ->paginate(50);
            
        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    });
    
    Route::get('/teleworking-logs', function (Request $request) {
        $logs = \App\Models\TeleworkingLog::with('teleworkingRequest.employee')
            ->when($request->teleworking_request_id, function ($query, $teleworkingRequestId) {
                return $query->where('teleworking_request_id', $teleworkingRequestId);
            })
            ->when($request->date_from, function ($query, $dateFrom) {
                return $query->whereDate('login_time', '>=', $dateFrom);
            })
            ->when($request->date_to, function ($query, $dateTo) {
                return $query->whereDate('login_time', '<=', $dateTo);
            })
            ->orderBy('login_time', 'desc')
            ->paginate(50);
            
        return response()->json([
            'success' => true,
            'data' => $logs
        ]);
    });
});

Route::get('/health', function () {
    return response()->json([
        'status' => 'ok',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});



// Get employee by ID
Route::get('/employees/{id}', function ($id) {
    try {
        $employee = DB::table('employees')
            ->where('id', $id)
            ->first();
        
        if (!$employee) {
            return response()->json([
                'success' => false,
                'message' => 'Employee not found'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $employee
        ]);
    } catch (Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error fetching employee: ' . $e->getMessage()
        ], 500);
    }
});















// Authentication endpoints
Route::post("/login", function (Request $request) {
    try {
        // Validate input
        $validated = $request->validate([
            "email" => "required|email",
            "password" => "required|string"
        ]);

        // Find user by email
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

        // Create session (simple implementation)
        $sessionId = session()->getId();
        
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
            "session_id" => $sessionId
        ]);

    } catch (ValidationException $e) {
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

Route::post("/logout", function (Request $request) {
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

Route::get("/user", function (Request $request) {
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

Route::get("/check-auth", function (Request $request) {
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

