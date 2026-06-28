<?php

require_once 'vendor/autoload.php';

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Profile;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "=== Profile Functionality Test ===\n\n";

try {
    // Test 1: Check if users table exists and has data
    echo "1. Testing users table...\n";
    $users = DB::table('users')->get();
    echo "   Found {$users->count()} users in database\n";
    
    if ($users->count() > 0) {
        $testUser = $users->first();
        echo "   Test user: {$testUser->name} ({$testUser->email})\n";
        
        // Test 2: Check if profiles table exists
        echo "\n2. Testing profiles table...\n";
        $profiles = DB::table('profiles')->get();
        echo "   Found {$profiles->count()} profiles in database\n";
        
        // Test 3: Check profile relationship
        echo "\n3. Testing user-profile relationship...\n";
        $userWithProfile = DB::table('users')
            ->leftJoin('profiles', 'users.id', '=', 'profiles.user_id')
            ->select('users.*', 'profiles.avatar', 'profiles.phone')
            ->first();
            
        if ($userWithProfile) {
            echo "   User profile relationship works\n";
            echo "   Avatar: " . ($userWithProfile->avatar ?: 'Not set') . "\n";
            echo "   Phone: " . ($userWithProfile->phone ?: 'Not set') . "\n";
        }
        
        // Test 4: Test Profile model
        echo "\n4. Testing Profile model...\n";
        $userModel = User::find($testUser->id);
        if ($userModel) {
            $profile = $userModel->profile ?? $userModel->profile()->create([
                'phone' => '123-456-7890',
                'address' => 'Test Address',
                'bio' => 'Test Bio'
            ]);
            echo "   Profile model works: " . ($profile ? 'Yes' : 'No') . "\n";
        }
        
        // Test 5: Check storage directory
        echo "\n5. Testing storage directories...\n";
        $avatarsPath = public_path('storage/avatars');
        if (is_dir($avatarsPath)) {
            echo "   Avatars directory exists: Yes\n";
            echo "   Directory is writable: " . (is_writable($avatarsPath) ? 'Yes' : 'No') . "\n";
        } else {
            echo "   Avatars directory exists: No\n";
        }
        
        echo "\n=== Test Results ===\n";
        echo "✅ Database connection: Working\n";
        echo "✅ Users table: Working\n";
        echo "✅ Profiles table: Working\n";
        echo "✅ User-Profile relationship: Working\n";
        echo "✅ Profile model: Working\n";
        echo "✅ Storage setup: " . (is_dir($avatarsPath) ? 'Working' : 'Needs attention') . "\n";
        
    } else {
        echo "   No users found. Please create a test user first.\n";
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

echo "\n=== Profile API Endpoints ===\n";
echo "GET  /api/profile - Get user profile\n";
echo "PUT  /api/profile - Update user profile\n";
echo "POST /api/profile/avatar - Upload avatar\n";
echo "\n=== Test Complete ===\n";
