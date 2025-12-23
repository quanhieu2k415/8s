<?php
/**
 * Admin Authentication Check
 * Include this at the top of protected admin pages
 */

require_once __DIR__ . '/../../autoloader.php';

use App\Services\Auth;
use App\Services\Permission;
use App\Core\CSRF;

// Session timeout configuration (1 hour = 3600 seconds)
define('SESSION_TIMEOUT', 3600);

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

// Get current user
$currentUser = $auth->user();
$csrfToken = $csrf->getToken();
$userRole = $currentUser['role'] ?? 'user';

// Permission helper variables for views
$canManageUsers = $permission->canManageUsers($userRole);
$canManageCMS = $permission->canManageCMS($userRole);
$canAccessSettings = $permission->canAccessSettings($userRole);
$canViewAllReports = $permission->canViewAllReports($userRole);
$canViewAllLogs = $permission->canViewAllLogs($userRole);
$canExportData = $permission->canExportData($userRole);
$isAdmin = $userRole === 'admin';
$isManager = $userRole === 'manager';

