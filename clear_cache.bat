@echo off
REM ========================================
REM Clear All Cache - Windows Server
REM ========================================

echo.
echo ======================================
echo   CLEAR CACHE - WINDOWS SERVER
echo ======================================
echo.

REM 1. Stop Apache
echo [1/6] Stopping Apache...
net stop Apache2.4 2>nul
if errorlevel 1 (
    echo WARNING: Could not stop Apache service. Please stop via XAMPP Control Panel.
    pause
) else (
    echo SUCCESS: Apache stopped.
)

REM 2. Clear PHP Session Files
echo.
echo [2/6] Clearing PHP Sessions...
if exist "C:\xampp\tmp\sess_*" (
    del /Q "C:\xampp\tmp\sess_*"
    echo SUCCESS: PHP sessions cleared.
) else (
    echo INFO: No session files found.
)

REM 3. Clear PHP OPcache (if exists)
echo.
echo [3/6] Clearing PHP OPcache files...
if exist "C:\xampp\tmp\php*" (
    del /Q "C:\xampp\tmp\php*"
    echo SUCCESS: OPcache files cleared.
) else (
    echo INFO: No OPcache files found.
)

REM 4. Clear Apache Cache (if exists)
echo.
echo [4/6] Clearing Apache cache...
if exist "C:\xampp\apache\logs\*.log" (
    REM Don't delete, just notify
    echo INFO: Log files exist at C:\xampp\apache\logs\
)

REM 5. Start Apache
echo.
echo [5/6] Starting Apache...
net start Apache2.4 2>nul
if errorlevel 1 (
    echo WARNING: Could not start Apache service. Please start via XAMPP Control Panel.
    pause
) else (
    echo SUCCESS: Apache started.
)

REM 6. Clear OPcache via PHP
echo.
echo [6/6] Clearing OPcache via PHP...

REM Create temp clear_cache.php file
echo ^<?php > C:\xampp\htdocs\web8s\clear_cache_temp.php
echo if (function_exists('opcache_reset')) { >> C:\xampp\htdocs\web8s\clear_cache_temp.php
echo     opcache_reset(); >> C:\xampp\htdocs\web8s\clear_cache_temp.php
echo     echo 'OPcache cleared successfully!'; >> C:\xampp\htdocs\web8s\clear_cache_temp.php
echo } else { >> C:\xampp\htdocs\web8s\clear_cache_temp.php
echo     echo 'OPcache not enabled'; >> C:\xampp\htdocs\web8s\clear_cache_temp.php
echo } >> C:\xampp\htdocs\web8s\clear_cache_temp.php
echo ?^> >> C:\xampp\htdocs\web8s\clear_cache_temp.php

REM Call the script via curl or just notify user
echo INFO: Temporary cache clear script created.
echo INFO: Please visit: http://localhost/web8s/clear_cache_temp.php
echo INFO: Then delete the file after use.

echo.
echo ======================================
echo   CACHE CLEARED!
echo ======================================
echo.
echo NEXT STEPS:
echo 1. Open browser in INCOGNITO mode (Ctrl+Shift+N)
echo 2. Visit: http://localhost/web8s/clear_cache_temp.php
echo 3. Then visit: http://localhost/web8s/admin
echo 4. Test your changes
echo.
echo Press any key to open browser...
pause >nul

start http://localhost/web8s/clear_cache_temp.php

echo.
echo Done! Remember to delete clear_cache_temp.php after testing.
pause
