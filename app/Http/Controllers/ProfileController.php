<?php

namespace App\Http\Controllers;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ProfileController extends Controller
{
    /**
     * Get the authenticated user's profile.
     */
    public function index(): JsonResponse
    {
        // Use session authentication like other endpoints
        if (!session('logged_in')) {
            return response()->json([
                'success' => false,
                'message' => 'Not authenticated'
            ], 401);
        }

        $userId = session('user_id');
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        $profile = $user->profile ?? $user->profile()->create([
            'phone' => null,
            'address' => null,
            'birth_date' => null,
            'gender' => null,
            'avatar' => null,
            'bio' => null,
            'country' => null,
            'city' => null,
            'postal_code' => null,
        ]);

        return response()->json([
            'success' => true,
            'user' => $user,
            'profile' => $profile,
        ]);
    }

    /**
     * Update the authenticated user's profile.
     */
    public function update(Request $request): JsonResponse
    {
        try {
            // Use session authentication like other endpoints
            if (!session('logged_in')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            $userId = session('user_id');
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $validated = $request->validate([
                'firstName' => 'required|string|max:255',
                'lastName' => 'required|string|max:255',
                'phone' => 'nullable|string|max:255',
                'address' => 'nullable|string',
                'birth_date' => 'nullable|date',
                'gender' => 'nullable|string|max:10',
                'avatar' => 'nullable|string|max:255',
                'bio' => 'nullable|string',
                'country' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:255',
                'postal_code' => 'nullable|string|max:20',
            ]);

            // Update user name
            $user->update([
                'name' => $validated['firstName'] . ' ' . $validated['lastName'],
            ]);

            // Update or create profile
            $profileData = [
                'phone' => $validated['phone'] ?? null,
                'address' => $validated['address'] ?? null,
                'birth_date' => $validated['birth_date'] ?? null,
                'gender' => $validated['gender'] ?? null,
                'avatar' => $validated['avatar'] ?? null,
                'bio' => $validated['bio'] ?? null,
                'country' => $validated['country'] ?? null,
                'city' => $validated['city'] ?? null,
                'postal_code' => $validated['postal_code'] ?? null,
            ];

            $profile = $user->profile()->updateOrCreate(
                ['user_id' => $user->id],
                $profileData
            );

            return response()->json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'user' => $user->fresh(),
                'profile' => $profile,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload avatar image.
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            // Use session authentication like other endpoints
            if (!session('logged_in')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Not authenticated'
                ], 401);
            }

            $userId = session('user_id');
            $user = User::find($userId);
            
            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            $validated = $request->validate([
                'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:800', // max 800KB
            ]);

            if ($request->hasFile('avatar')) {
                $file = $request->file('avatar');
                
                // Create avatar directory if it doesn't exist
                $avatarPath = public_path('avatars');
                if (!file_exists($avatarPath)) {
                    mkdir($avatarPath, 0755, true);
                }
                
                // Generate unique filename
                $filename = 'avatar_' . $user->id . '_' . time() . '.' . $file->getClientOriginalExtension();
                
                // Move the file to public directory
                $file->move($avatarPath, $filename);
                
                // Get the URL
                $avatarUrl = '/avatars/' . $filename;
                
                // Update user's profile avatar
                $profile = $user->profile ?? $user->profile()->create();
                $profile->update(['avatar' => $avatarUrl]);

                return response()->json([
                    'success' => true,
                    'message' => 'Avatar uploaded successfully',
                    'avatar_url' => $avatarUrl,
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => 'No file uploaded'
            ], 400);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to upload avatar',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
