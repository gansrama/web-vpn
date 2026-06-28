<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Get all notifications for the current user
     */
    public function index(Request $request)
    {
        try {
            $notifications = DB::table('notifications')
                ->where('target_type', 'all')
                ->orWhereNull('target_type')
                ->orderBy('created_at', 'desc')
                ->limit(50)
                ->get();

            return response()->json([
                'success' => true,
                'notifications' => $notifications
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching notifications: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching notifications'
            ], 500);
        }
    }

    /**
     * Get unread notification count
     */
    public function unreadCount(Request $request)
    {
        try {
            // Since the table doesn't have a 'read' column, return 0 for now
            // In a real implementation, you might track read status differently
            $count = 0;

            return response()->json([
                'success' => true,
                'count' => $count
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching unread count: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'count' => 0
            ], 500);
        }
    }

    /**
     * Mark notification as read (not implemented due to table structure)
     */
    public function markAsRead($id)
    {
        try {
            // Since the table doesn't have a 'read' column, this is not implemented
            // In a real implementation, you might use a separate table to track read status
            return response()->json([
                'success' => true,
                'message' => 'Notification marked as read (not implemented)'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking notification as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error marking notification as read'
            ], 500);
        }
    }

    /**
     * Mark all notifications as read (not implemented due to table structure)
     */
    public function markAllAsRead(Request $request)
    {
        try {
            // Since the table doesn't have a 'read' column, this is not implemented
            // In a real implementation, you might use a separate table to track read status
            return response()->json([
                'success' => true,
                'message' => 'All notifications marked as read (not implemented)'
            ]);
        } catch (\Exception $e) {
            Log::error('Error marking all notifications as read: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error marking all notifications as read'
            ], 500);
        }
    }

    /**
     * Add form submission notification
     */
    public function addFormSubmissionNotification(Request $request)
    {
        try {
            $validated = $request->validate([
                'formType' => 'required|string',
                'email' => 'required|email',
                'requestId' => 'required|string',
                'action' => 'required|string'
            ], [
                'requestId.required' => 'The request id field is required.',
                'requestId.string' => 'The request id field must be a string.'
            ]);

            // Create notification using existing table structure
            $notificationData = [
                'title' => 'Form ' . $validated['action'],
                'message' => ucfirst($validated['formType']) . ' form submitted by ' . $validated['email'] . ' - Request ID: ' . $validated['requestId'],
                'type' => 'success',
                'target_type' => 'all',
                'is_global' => 0,
                'data' => json_encode([
                    'formType' => $validated['formType'],
                    'email' => $validated['email'],
                    'requestId' => $validated['requestId'],
                    'action' => $validated['action']
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ];

            $notificationId = DB::table('notifications')->insertGetId($notificationData);

            // Broadcast notification if table exists
            if (\Schema::hasTable('notification_broadcasts')) {
                DB::table('notification_broadcasts')->insert([
                    'notification_id' => $notificationId,
                    'payload' => json_encode(array_merge($notificationData, [
                        'id' => $notificationId,
                        'created_at' => now()->toISOString()
                    ])),
                    'created_at' => now()
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification added successfully',
                'notification_id' => $notificationId
            ]);
        } catch (\Exception $e) {
            Log::error('Error adding form submission notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get broadcast notifications
     */
    public function getBroadcasts(Request $request)
    {
        try {
            $broadcasts = DB::table('notification_broadcasts')
                ->orderBy('created_at', 'desc')
                ->limit(100)
                ->get();

            return response()->json([
                'success' => true,
                'broadcasts' => $broadcasts
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching broadcasts: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error fetching broadcasts'
            ], 500);
        }
    }

    /**
     * Create global notification (admin only)
     */
    public function createGlobal(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'message' => 'required|string',
                'type' => 'required|in:info,success,warning,error'
            ]);

            // Get all users
            $users = DB::table('users')->get();

            foreach ($users as $user) {
                $notificationId = DB::table('notifications')->insertGetId([
                    'user_id' => $user->id,
                    'title' => $validated['title'],
                    'message' => $validated['message'],
                    'type' => $validated['type'],
                    'read' => false,
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                // Broadcast notification
                if (\Schema::hasTable('notification_broadcasts')) {
                    DB::table('notification_broadcasts')->insert([
                        'notification_id' => $notificationId,
                        'payload' => json_encode([
                            'id' => $notificationId,
                            'user_id' => $user->id,
                            'title' => $validated['title'],
                            'message' => $validated['message'],
                            'type' => $validated['type'],
                            'read' => false,
                            'created_at' => now()->toISOString()
                        ]),
                        'created_at' => now()
                    ]);
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Global notification created successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Error creating global notification: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error creating global notification'
            ], 500);
        }
    }

    /**
     * Helper method to add form submission notification without Request object
     * This can be called directly from other controllers
     */
    public static function createFormSubmissionNotificationStatic($formType, $email, $requestId, $action = 'disubmit')
    {
        try {
            $notificationData = [
                'title' => 'Form ' . $action,
                'message' => ucfirst($formType) . ' form submitted by ' . $email . ' - Request ID: ' . $requestId,
                'type' => 'success',
                'target_type' => 'all',
                'is_global' => 0,
                'data' => json_encode([
                    'formType' => $formType,
                    'email' => $email,
                    'requestId' => $requestId,
                    'action' => $action
                ]),
                'created_at' => now(),
                'updated_at' => now()
            ];

            $notificationId = DB::table('notifications')->insertGetId($notificationData);

            // Broadcast notification if table exists
            if (\Schema::hasTable('notification_broadcasts')) {
                DB::table('notification_broadcasts')->insert([
                    'notification_id' => $notificationId,
                    'payload' => json_encode(array_merge($notificationData, [
                        'id' => $notificationId,
                        'created_at' => now()->toISOString()
                    ])),
                    'created_at' => now()
                ]);
            }

            Log::info('Form submission notification created successfully', [
                'notification_id' => $notificationId,
                'form_type' => $formType,
                'request_id' => $requestId
            ]);

            return $notificationId;
        } catch (\Exception $e) {
            Log::error('Error creating form submission notification: ' . $e->getMessage());
            return null;
        }
    }
}
