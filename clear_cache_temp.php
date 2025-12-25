<?php
header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Clear Cache</title>
    <style>
        body { font-family: Arial; max-width: 600px; margin: 50px auto; padding: 20px; }
        .success { color: green; }
        .info { color: blue; }
        .warning { color: orange; }
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>🧹 Cache Clear Report</h1>
    
    <h2>1. OPcache Status</h2>
    <?php
    if (function_exists('opcache_reset')) {
        $before = opcache_get_status(false);
        opcache_reset();
        $after = opcache_get_status(false);
        
        echo '<p class="success">✅ OPcache cleared successfully!</p>';
        echo '<pre>';
        echo "Before:\n";
        echo "  - Cached scripts: " . ($before['opcache_statistics']['num_cached_scripts'] ?? 0) . "\n";
        echo "  - Memory used: " . number_format(($before['memory_usage']['used_memory'] ?? 0) / 1024 / 1024, 2) . " MB\n\n";
        echo "After:\n";
        echo "  - Cached scripts: " . ($after['opcache_statistics']['num_cached_scripts'] ?? 0) . "\n";
        echo "  - Memory used: " . number_format(($after['memory_usage']['used_memory'] ?? 0) / 1024 / 1024, 2) . " MB\n";
        echo '</pre>';
    } else {
        echo '<p class="info">ℹ️  OPcache not enabled</p>';
    }
    ?>
    
    <h2>2. Realpath Cache</h2>
    <?php
    clearstatcache(true);
    echo '<p class="success">✅ Realpath cache cleared</p>';
    ?>
    
    <h2>3. PHP Info</h2>
    <pre><?php
    echo "PHP Version: " . phpversion() . "\n";
    echo "Server Time: " . date('Y-m-d H:i:s') . "\n";
    echo "SAPI: " . php_sapi_name() . "\n";
    ?></pre>
    
    <h2>Next Steps:</h2>
    <ol>
        <li><strong>Close this tab</strong></li>
        <li><strong>Open INCOGNITO window</strong> (Ctrl + Shift + N)</li>
        <li><strong>Visit admin page:</strong> <a href="/web8s/admin" target="_blank">http://localhost/web8s/admin</a></li>
        <li><strong>Test your changes</strong></li>
        <li><strong>Delete this file</strong> when done</li>
    </ol>
    
    <p class="warning">⚠️  <strong>IMPORTANT:</strong> Delete this file (clear_cache_temp.php) after use!</p>
    
    <hr>
    <p><em>Generated at <?php echo date('Y-m-d H:i:s'); ?></em></p>
</body>
</html>
