<?php
/**
 * Debug API to test permissions directly
 */

require_once __DIR__ . '/backend_api/bootstrap.php';

use App\Core\Database;
use App\Services\Permission;

header('Content-Type: application/json');

try {
    $permission = Permission::getInstance();
    
    // Get all permissions
    $allPerms = $permission->getAllPermissions();
    $matrix = $permission->getPermissionMatrix();
    
    // Group by category
    $grouped = [];
    foreach ($allPerms as $perm) {
        $category = $perm['category'] ?? 'general';
        if (!isset($grouped[$category])) {
            $categoryNames = [
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
            $categoryIcons = [
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
            $grouped[$category] = [
                'name' => $categoryNames[$category] ?? ucfirst($category),
                'icon' => $categoryIcons[$category] ?? 'extension',
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
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
}
