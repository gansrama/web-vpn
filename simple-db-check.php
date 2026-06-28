<?php
// Simple database check without Laravel bootstrap
try {
    // Try to connect to database using PDO
    $host = 'localhost';
    $dbname = 'webform_vpn'; // Adjust if needed
    $username = 'root'; // Adjust if needed
    $password = ''; // Adjust if needed
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "<h1>Database Connection Test</h1>";
    echo "<p style='color: green;'>✓ Database connection successful!</p>";
    
    // Check if table exists
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'akses_logic_requests'");
    $stmt->execute();
    $tables = $stmt->fetchAll();
    
    if (empty($tables)) {
        echo "<p style='color: red;'>✗ Table 'akses_logic_requests' does not exist!</p>";
    } else {
        echo "<p style='color: green;'>✓ Table 'akses_logic_requests' exists!</p>";
        
        // Check count
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM akses_logic_requests");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p>Total records: " . $result['count'] . "</p>";
        
        // Show recent records
        $stmt = $pdo->prepare("SELECT id, nama_sistem, status, created_at FROM akses_logic_requests ORDER BY created_at DESC LIMIT 5");
        $stmt->execute();
        $records = $stmt->fetchAll();
        
        if (!empty($records)) {
            echo "<h3>Recent Records:</h3>";
            echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
            echo "<tr><th>ID</th><th>Nama Sistem</th><th>Status</th><th>Created At</th></tr>";
            
            foreach ($records as $record) {
                echo "<tr>";
                echo "<td>" . $record['id'] . "</td>";
                echo "<td>" . $record['nama_sistem'] . "</td>";
                echo "<td>" . $record['status'] . "</td>";
                echo "<td>" . $record['created_at'] . "</td>";
                echo "</tr>";
            }
            
            echo "</table>";
        } else {
            echo "<p>No records found in table.</p>";
        }
    }
    
    // Check employees table
    $stmt = $pdo->prepare("SHOW TABLES LIKE 'employees'");
    $stmt->execute();
    $empTables = $stmt->fetchAll();
    
    if (!empty($empTables)) {
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM employees");
        $stmt->execute();
        $result = $stmt->fetch();
        echo "<p>Employees in database: " . $result['count'] . "</p>";
    }
    
} catch (PDOException $e) {
    echo "<h1>Database Connection Error</h1>";
    echo "<p style='color: red;'>✗ Database connection failed!</p>";
    echo "<p>Error: " . $e->getMessage() . "</p>";
    
    echo "<h2>Troubleshooting:</h2>";
    echo "<ul>";
    echo "<li>Check if MySQL/XAMPP is running</li>";
    echo "<li>Verify database name: 'webform_vpn'</li>";
    echo "<li>Verify username/password: root/empty</li>";
    echo "<li>Check if database exists</li>";
    echo "</ul>";
} catch (Exception $e) {
    echo "<h1>General Error</h1>";
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
