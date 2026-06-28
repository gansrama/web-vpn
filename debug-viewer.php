<?php
// Debug log viewer
$logFile = __DIR__ . '/storage/logs/debug.log';
echo "<h1>Debug Log Viewer</h1>";
echo "<h2>Latest debug logs:</h2>";
echo "<pre style='background: #f5f5f5; padding: 10px; font-family: monospace; white-space: pre-wrap; color: #333;'>";

if (file_exists($logFile)) {
    $content = file_get_contents($logFile);
    echo htmlspecialchars($content);
} else {
    echo "Debug log file not found at: " . $logFile;
}

echo "</pre>";
echo "<br><br>";
echo "<button onclick='location.reload()'>Refresh</button>";
echo "<button onclick='document.getElementById(\"log\").value=\"\"; document.forms[0].submit()'>Clear Log</button>";
?>
<form method="post">
    <input type="hidden" name="clear" id="log">
</form>
<?php
if ($_POST['clear']) {
    file_put_contents($logFile, '');
    echo "<script>location.reload();</script>";
}
?>
