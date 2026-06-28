<?php
// Simple log viewer
$logFile = __DIR__ . '/storage/logs/laravel.log';
echo "<h1>Laravel Log Viewer</h1>";
echo "<h2>Last 100 lines:</h2>";
echo "<pre style='background: #f5f5f5; padding: 10px; font-family: monospace; white-space: pre-wrap;'>";

if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    $lines = explode("\n", $content);
    $lastLines = array_slice($lines, -100);
    foreach ($lastLines as $line) {
        echo htmlspecialchars($line) . "\n";
    }
} else {
    echo "Log file not found at: " . $logFile;
}

echo "</pre>";
?>
