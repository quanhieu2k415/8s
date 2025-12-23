<?php
/**
 * Permissions API
 * Manage role permissions
 */

// Error handling
error_reporting(E_ALL);
ini_set('display_errors', 0);

set_exception_handler(function($e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Server Error: ' . $e->getMessage()
    ]);
    exit;
});

require_once __DIR__ . '/bootstrap.php';

use App\Core\CSRF;
use App\Core\Database;
use App\Services\Auth;
use App\Services\Permission;

header('Content-Type: application/json');

try {
    $auth = Auth::getInstance();
    $permission = Permission::getInstance();
    $csrf = new CSRF();

    // Check authentication
    if (!$auth->check()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit;
    }

    $currentUser = $auth->user();
    $userRole = $currentUser['role'] ?? 'user';

    // Only admin can manage permissions
    if ($userRole !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Không có quyền truy cập']);
        exit;
    }

    $action = $_GET['action'] ?? '';

    switch ($action) {
        case 'matrix':
            getPermissionMatrix($permission);
            break;
            
        case 'update':
            updatePermissionSimple($csrf);
            break;
            
        case 'categories':
            getPermissionsByCategory($permission);
            break;
            
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false, 
        'message' => 'Error: ' . $e->getMessage()
    ]);
}

/**
 * Get permission matrix for all roles
 */
function getPermissionMatrix($permission) {
    try {
        $matrix = $permission->getPermissionMatrix();
        $permissions = $permission->getAllPermissions();
        
        // Check if permissions exist
        if (empty($permissions)) {
            echo json_encode([
                'success' => false,
                'message' => 'Chưa có dữ liệu permissions. Vui lòng chạy file SQL: backend_api/database/permission_migration.sql trong phpMyAdmin'
            ]);
            return;
        }
        
        // Group by category
        $grouped = [];
        foreach ($permissions as $perm) {
            $category = $perm['category'] ?? 'general';
            if (!isset($grouped[$category])) {
                $grouped[$category] = [
                    'name' => getCategoryName($category),
                    'icon' => getCategoryIcon($category),
                    'permissions' => []
                ];
            }
            
            $key = $perm['permission_key'];
            $grouped[$category]['permissions'][] = [
                'key' => $key,
                'name' => $perm['permission_name'],
                'description' => $perm['description'],
                'manager' => $matrix[$key]['roles']['manager'] ?? false,
                'user' => $matrix[$key]['roles']['user'] ?? false
            ];
        }
        
        echo json_encode([
            'success' => true,
            'data' => $grouped
        ]);
    } catch (Exception $e) {
        // Check if it's a table not found error
        $errorMessage = $e->getMessage();
        if (strpos($errorMessage, 'permissions') !== false || strpos($errorMessage, "doesn't exist") !== false) {
            echo json_encode([
                'success' => false,
                'message' => 'Bảng permissions chưa tồn tại. Vui lòng import file SQL: backend_api/database/permission_migration.sql trong phpMyAdmin'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => $errorMessage
            ]);
        }
    }
}

/**
 * Get permissions by category
 */
function getPermissionsByCategory($permission) {
    try {
        $grouped = $permission->getPermissionsByCategory();
        echo json_encode([
            'success' => true,
            'data' => $grouped
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Update a permission (grant or revoke)
 */
function updatePermission($permission, $csrf) {
    // Verify CSRF
    $headers = getallheaders();
    $csrfToken = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? '';
    
    if (!$csrf->validate($csrfToken)) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        return;
    }
    
    // Get request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    $role = $input['role'] ?? '';
    $permissionKey = $input['permission_key'] ?? '';
    $granted = $input['granted'] ?? false;
    
    // Validate role (can't modify admin permissions)
    if (!in_array($role, ['manager', 'user'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid role']);
        return;
    }
    
    if (empty($permissionKey)) {
        echo json_encode(['success' => false, 'message' => 'Permission key is required']);
        return;
    }
    
    try {
        if ($granted) {
            $result = $permission->grantPermission($role, $permissionKey);
        } else {
            $result = $permission->revokePermission($role, $permissionKey);
        }
        
        if ($result) {
            echo json_encode([
                'success' => true,
                'message' => $granted ? 'Đã cấp quyền thành công' : 'Đã thu hồi quyền thành công'
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Không thể cập nhật quyền'
            ]);
        }
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

/**
 * Simple permission update using direct database
 */
function updatePermissionSimple($csrf) {
    // Verify CSRF
    $headers = getallheaders();
    $csrfToken = $headers['X-CSRF-Token'] ?? $headers['x-csrf-token'] ?? '';
    
    if (!$csrf->validate($csrfToken)) {
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        return;
    }
    
    // Get request body
    $input = json_decode(file_get_contents('php://input'), true);
    
    $role = $input['role'] ?? '';
    $permissionKey = $input['permission_key'] ?? '';
    $granted = $input['granted'] ?? false;
    
    // Validate role
    if (!in_array($role, ['manager', 'user'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid role']);
        return;
    }
    
    if (empty($permissionKey)) {
        echo json_encode(['success' => false, 'message' => 'Permission key is required']);
        return;
    }
    
    try {
        $db = Database::getInstance();
        
        if ($granted) {
            // Grant permission
            $sql = "INSERT IGNORE INTO role_permissions (role, permission_key) VALUES (:role, :permission_key)";
            $db->execute($sql, [':role' => $role, ':permission_key' => $permissionKey]);
        } else {
            // Revoke permission
            $sql = "DELETE FROM role_permissions WHERE role = :role AND permission_key = :permission_key";
            $db->execute($sql, [':role' => $role, ':permission_key' => $permissionKey]);
        }
        
        echo json_encode([
            'success' => true,
            'message' => $granted ? 'Đã cấp quyền thành công' : 'Đã thu hồi quyền thành công'
        ]);
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
}

/**
 * Get category display name
 */
function getCategoryName($category) {
    $names = [
        'users' => 'Quản lý Tài khoản',
        'settings' => 'Cài đặt Hệ thống',
        'reports' => 'Báo cáo',
        'logs' => 'Activity Logs',
        'content' => 'Quản lý Nội dung',
        'news' => 'Tin tức',
        'registrations' => 'Đăng ký Tư vấn',
        'cms' => 'CMS',
        'profile' => 'Hồ sơ Cá nhân',
        'database' => 'Database',
        'general' => 'Chung'
    ];
    return $names[$category] ?? ucfirst($category);
}

/**
 * Get category icon
 */
function getCategoryIcon($category) {
    $icons = [
        'users' => 'group',
        'settings' => 'settings',
        'reports' => 'analytics',
        'logs' => 'history',
        'content' => 'edit_note',
        'news' => 'article',
        'registrations' => 'people',
        'cms' => 'dashboard_customize',
        'profile' => 'person',
        'database' => 'storage',
        'general' => 'extension'
    ];
    return $icons[$category] ?? 'extension';
}
