# ========================================
# Clear All Cache - PowerShell Version
# ========================================

Write-Host "`n=====================================" -ForegroundColor Cyan
Write-Host "  CLEAR CACHE - WINDOWS SERVER" -ForegroundColor Cyan
Write-Host "=====================================`n" -ForegroundColor Cyan

# 1. Stop Apache
Write-Host "[1/7] Stopping Apache..." -ForegroundColor Yellow
try {
    Stop-Service -Name Apache2.4 -ErrorAction Stop
    Write-Host "✅ Apache stopped." -ForegroundColor Green
} catch {
    Write-Host "⚠️  Could not stop Apache service. Please stop via XAMPP Control Panel." -ForegroundColor Red
}

# 2. Clear PHP Session Files
Write-Host "`n[2/7] Clearing PHP Sessions..." -ForegroundColor Yellow
$sessionFiles = Get-ChildItem "C:\xampp\tmp\sess_*" -ErrorAction SilentlyContinue
if ($sessionFiles) {
    Remove-Item "C:\xampp\tmp\sess_*" -Force
    Write-Host "✅ Cleared $($sessionFiles.Count) session files." -ForegroundColor Green
} else {
    Write-Host "ℹ️  No session files found." -ForegroundColor Gray
}

# 3. Clear PHP OPcache files
Write-Host "`n[3/7] Clearing PHP OPcache files..." -ForegroundColor Yellow
$opcacheFiles = Get-ChildItem "C:\xampp\tmp\php*" -ErrorAction SilentlyContinue
if ($opcacheFiles) {
    Remove-Item "C:\xampp\tmp\php*" -Force
    Write-Host "✅ Cleared $($opcacheFiles.Count) OPcache files." -ForegroundColor Green
} else {
    Write-Host "ℹ️  No OPcache files found." -ForegroundColor Gray
}

# 4. Clear temp files
Write-Host "`n[4/7] Clearing temp files..." -ForegroundColor Yellow
$tempFiles = Get-ChildItem "C:\xampp\tmp\*" -File -ErrorAction SilentlyContinue
if ($tempFiles) {
    Remove-Item "C:\xampp\tmp\*" -Force -ErrorAction SilentlyContinue
    Write-Host "✅ Temp files cleared." -ForegroundColor Green
}

# 5. Start Apache
Write-Host "`n[5/7] Starting Apache..." -ForegroundColor Yellow
try {
    Start-Service -Name Apache2.4 -ErrorAction Stop
    Write-Host "✅ Apache started." -ForegroundColor Green
} catch {
    Write-Host "⚠️  Could not start Apache service. Please start via XAMPP Control Panel." -ForegroundColor Red
}

# 6. Create PHP script to clear OPcache
Write-Host "`n[6/7] Creating OPcache clear script..." -ForegroundColor Yellow
$clearCacheContent = @"
<?php
header('Content-Type: text/plain');

echo "=== CACHE CLEAR REPORT ===\n\n";

// 1. Clear OPcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "✅ OPcache cleared successfully!\n";
    
    // Show OPcache status
    `$status = opcache_get_status(false);
    if (`$status) {
        echo "   - Memory used: " . number_format(`$status['memory_usage']['used_memory'] / 1024 / 1024, 2) . " MB\n";
        echo "   - Cached scripts: " . `$status['opcache_statistics']['num_cached_scripts'] . "\n";
    }
} else {
    echo "ℹ️  OPcache not enabled\n";
}

// 2. Clear Realpath Cache
echo "\n✅ Realpath cache cleared\n";
clearstatcache(true);

// 3. Show PHP version
echo "\n📋 PHP Version: " . phpversion() . "\n";
echo "📋 Server Time: " . date('Y-m-d H:i:s') . "\n";

echo "\n=== DONE ===\n";
echo "\n🌐 Now reload your admin page in INCOGNITO mode!\n";
echo "🗑️  Remember to delete this file after use.\n";
?>
"@

$clearCachePath = "C:\xampp\htdocs\web8s\clear_cache_temp.php"
$clearCacheContent | Out-File -FilePath $clearCachePath -Encoding UTF8 -Force
Write-Host "✅ Created: $clearCachePath" -ForegroundColor Green

# 7. Call the script
Write-Host "`n[7/7] Clearing OPcache via PHP..." -ForegroundColor Yellow
Start-Sleep -Seconds 2

try {
    $response = Invoke-WebRequest -Uri "http://localhost/web8s/clear_cache_temp.php" -UseBasicParsing -TimeoutSec 5
    Write-Host $response.Content -ForegroundColor Cyan
} catch {
    Write-Host "⚠️  Could not call clear script via web. Please visit manually:" -ForegroundColor Yellow
    Write-Host "   http://localhost/web8s/clear_cache_temp.php" -ForegroundColor White
}

# Summary
Write-Host "`n=====================================" -ForegroundColor Cyan
Write-Host "  ✅ CACHE CLEARED!" -ForegroundColor Green
Write-Host "=====================================`n" -ForegroundColor Cyan

Write-Host "📋 NEXT STEPS:" -ForegroundColor Yellow
Write-Host "1. Open browser in INCOGNITO mode (Ctrl+Shift+N)" -ForegroundColor White
Write-Host "2. Visit: http://localhost/web8s/admin" -ForegroundColor White
Write-Host "3. Test your changes" -ForegroundColor White
Write-Host "4. Delete clear_cache_temp.php when done`n" -ForegroundColor White

# Offer to open browser
$openBrowser = Read-Host "Open browser now? (y/n)"
if ($openBrowser -eq 'y' -or $openBrowser -eq 'Y') {
    Start-Process "http://localhost/web8s/admin"
}

Write-Host "`n✅ Done!`n" -ForegroundColor Green
