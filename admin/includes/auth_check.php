<?php
/**
 * Admin Authentication Check
 * Include this at the top of protected admin pages
 */

require_once __DIR__ . '/../../autoloader.php';

use App\Services\Auth;
use App\Services\Permission;
use App\Core\CSRF;

// Session timeout configuration
// Try to load from CMS settings, fallback to 30 minutes (1800 seconds)
$sessionTimeoutMinutes = 30; // Default
try {
    $db = \App\Core\Database::getInstance();
    $timeoutSetting = $db->fetchAll(
        "SELECT content_value FROM content_pages WHERE section_key = 'security_session_timeout' LIMIT 1"
    );
    if (!empty($timeoutSetting) && isset($timeoutSetting[0]['content_value'])) {
        $sessionTimeoutMinutes = (int)$timeoutSetting[0]['content_value'];
        // Ensure it's within reasonable range (5-1440 minutes = 1 day)
        if ($sessionTimeoutMinutes < 5) $sessionTimeoutMinutes = 5;
        if ($sessionTimeoutMinutes > 1440) $sessionTimeoutMinutes = 1440;
    }
} catch (Exception $e) {
    // If error loading setting, use default
    error_log("Error loading session timeout setting: " . $e->getMessage());
}

define('SESSION_TIMEOUT', $sessionTimeoutMinutes * 60); // Convert to seconds

$auth = Auth::getInstance();
$permission = Permission::getInstance();
$csrf = new CSRF();

// Check session timeout
if (isset($_SESSION['last_activity'])) {
    $inactive_time = time() - $_SESSION['last_activity'];
    
    if ($inactive_time > SESSION_TIMEOUT) {
        // Session expired - logout and redirect with message
        $auth->logout();
        header('Location: index.php?expired=1');
        exit;
    }
}

// Update last activity time
$_SESSION['last_activity'] = time();

// Check authentication
$auth->requireAuth('index.php');

// Verify session token (single session login)
if (!$auth->verifySessionToken()) {
    // Session token invalid - user logged in from another device
    $auth->logout();
    header('Location: index.php?kicked=1');
    exit;
}

// Get current user
$currentUser = $auth->user();
$csrfToken = $csrf->getToken();
$userRole = $currentUser['role'] ?? 'user';

// Permission helper variables for views
$canManageUsers = $permission->canManageUsers($userRole);
$canManageCMS = $permission->canManageCMS($userRole);
$canManageContentBlocks = $permission->canManageContentBlocks($userRole);
$canAccessSettings = $permission->canAccessSettings($userRole);
$canViewAllReports = $permission->canViewAllReports($userRole);
$canViewAllLogs = $permission->canViewAllLogs($userRole);
$canExportData = $permission->canExportData($userRole);
$isAdmin = $userRole === 'admin';
$isManager = $userRole === 'manager';

