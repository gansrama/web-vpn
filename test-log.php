<?php
// Read the last 50 lines of Laravel log
$logFile = __DIR__ . '/storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -50);
    echo "=== Last 50 lines of Laravel log ===\n";
    foreach ($lastLines as $line) {
        echo $line;
    }
} else {
    echo "Log file not found\n";
}
?>
