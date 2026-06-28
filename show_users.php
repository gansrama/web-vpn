<?php

// Simple script to display users without Laravel framework
$databasePath = __DIR__ . '/database/database.sqlite';

try {
    $pdo = new PDO('sqlite:' . $databasePath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "=== USERS TABLE DATA ===\n\n";
    
    $stmt = $pdo->query("SELECT id, name, email, email_verified_at, created_at FROM users ORDER BY id");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    if (empty($users)) {
        echo "No users found in the database.\n";
    } else {
        echo "Total users: " . count($users) . "\n\n";
        
        foreach ($users as $user) {
            echo "ID: {$user['id']}\n";
            echo "Name: {$user['name']}\n";
            echo "Email: {$user['email']}\n";
            echo "Email Verified: " . ($user['email_verified_at'] ? 'Yes' : 'No') . "\n";
            echo "Created: {$user['created_at']}\n";
            echo "-------------------\n";
        }
        
        echo "\nLogin Credentials:\n";
        echo "All users have password: 'password'\n\n";
        
        echo "Test Accounts:\n";
        foreach ($users as $user) {
            echo "- Email: {$user['email']}, Password: password\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}

echo "\n=== END ===\n";
