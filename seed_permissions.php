<?php
/**
 * Seed Permissions Script
 * Run this script to add all permissions to the database
 * URL: http://localhost/web8s/seed_permissions.php
 */

require_once __DIR__ . '/autoloader.php';

use App\Core\Database;

header('Content-Type: text/html; charset=utf-8');

echo "<h1>🔒 Seed Permissions</h1>";

try {
    $db = Database::getInstance();
    
    // Create permissions table if not exists
    $createTable = "
    CREATE TABLE IF NOT EXISTS permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        permission_key VARCHAR(100) UNIQUE NOT NULL,
        permission_name VARCHAR(255) NOT NULL,
        description TEXT,
        category VARCHAR(50) DEFAULT 'general',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $db->execute($createTable);
    echo "<p style='color:green'>✅ Bảng permissions đã sẵn sàng</p>";
    
    // Create role_permissions table if not exists
    $createRolePerms = "
    CREATE TABLE IF NOT EXISTS role_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        role ENUM('admin', 'manager', 'user') NOT NULL,
        permission_key VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_role_permission (role, permission_key)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ";
    $db->execute($createRolePerms);
    echo "<p style='color:green'>✅ Bảng role_permissions đã sẵn sàng</p>";
    
    // Define all permissions
    $permissions = [
        // Users Management
        ['users.view_all', 'Xem tất cả users', 'Xem danh sách tất cả tài khoản trong hệ thống', 'users'],
        ['users.view_team', 'Xem users trong team', 'Xem danh sách users được gán quản lý', 'users'],
        ['users.create_admin', 'Tạo tài khoản Admin', 'Tạo tài khoản với quyền Admin', 'users'],
        ['users.create_manager', 'Tạo tài khoản Manager', 'Tạo tài khoản với quyền Manager', 'users'],
        ['users.create_user', 'Tạo tài khoản User', 'Tạo tài khoản với quyền User', 'users'],
        ['users.edit_all', 'Sửa tất cả users', 'Chỉnh sửa thông tin mọi tài khoản', 'users'],
        ['users.edit_team', 'Sửa users trong team', 'Chỉnh sửa thông tin users được gán quản lý', 'users'],
        ['users.delete', 'Xóa tài khoản', 'Xóa tài khoản khỏi hệ thống', 'users'],
        
        // Settings
        ['settings.view', 'Xem cấu hình', 'Xem các cấu hình hệ thống', 'settings'],
        ['settings.modify', 'Thay đổi cấu hình', 'Thay đổi cấu hình hệ thống', 'settings'],
        
        // Reports
        ['reports.view_all', 'Xem tất cả báo cáo', 'Xem thống kê chi tiết toàn hệ thống (📊)', 'reports'],
        ['reports.view_team', 'Xem báo cáo team', 'Xem báo cáo của team được quản lý', 'reports'],
        ['reports.view_personal', 'Xem báo cáo cá nhân', 'Xem báo cáo của bản thân', 'reports'],
        ['reports.export', 'Xuất báo cáo', 'Xuất báo cáo ra file', 'reports'],
        
        // Logs
        ['logs.view_all', 'Xem tất cả logs', 'Xem activity logs toàn hệ thống', 'logs'],
        ['logs.view_team', 'Xem logs team', 'Xem activity logs của team', 'logs'],
        
        // Content Management
        ['content.manage_all', 'Quản lý tất cả nội dung', 'Quản lý nội dung toàn website', 'content'],
        ['content.manage_assigned', 'Quản lý nội dung được gán', 'Quản lý nội dung trong phạm vi được gán', 'content'],
        ['content.view', 'Xem nội dung', 'Xem nội dung website', 'content'],
        
        // News
        ['news.create', 'Tạo tin tức', 'Tạo bài viết tin tức mới', 'news'],
        ['news.edit_all', 'Sửa tất cả tin tức', 'Sửa mọi bài viết tin tức', 'news'],
        ['news.edit_own', 'Sửa tin tức của mình', 'Sửa bài viết do mình tạo', 'news'],
        ['news.delete', 'Xóa tin tức', 'Xóa bài viết tin tức', 'news'],
        ['news.publish', 'Đăng tin tức', 'Đăng/gỡ bài viết tin tức', 'news'],
        
        // Registrations
        ['registrations.view_all', 'Xem tất cả đăng ký', 'Xem tất cả đăng ký tư vấn', 'registrations'],
        ['registrations.view_assigned', 'Xem đăng ký được gán', 'Xem đăng ký trong phạm vi được gán', 'registrations'],
        ['registrations.edit', 'Sửa đăng ký', 'Chỉnh sửa thông tin đăng ký', 'registrations'],
        ['registrations.delete', 'Xóa đăng ký', 'Xóa đăng ký khỏi hệ thống', 'registrations'],
        ['registrations.export', 'Xuất đăng ký', 'Xuất danh sách đăng ký ra file', 'registrations'],
        
        // CMS
        ['cms.manage', 'Quản lý CMS', 'Quản lý nội dung CMS website', 'cms'],
        ['cms.images', 'Quản lý hình ảnh', 'Upload và quản lý hình ảnh', 'cms'],
        ['cms.texts', 'Quản lý văn bản', 'Chỉnh sửa văn bản trên website', 'cms'],
        
        // Content Blocks
        ['content_blocks.view', 'Xem Content Blocks', 'Xem danh sách content blocks', 'content_blocks'],
        ['content_blocks.manage', 'Quản lý Content Blocks', 'Tạo, sửa, xóa content blocks', 'content_blocks'],
        
        // Profile
        ['profile.edit_own', 'Sửa thông tin cá nhân', 'Chỉnh sửa thông tin cá nhân của mình', 'profile'],
        ['profile.change_password', 'Đổi mật khẩu', 'Đổi mật khẩu tài khoản của mình', 'profile'],
        
        // Database
        ['database.backup', 'Backup database', 'Tạo bản backup database', 'database'],
        ['database.restore', 'Restore database', 'Khôi phục database từ backup', 'database'],
    ];
    
    // Insert permissions
    $insertedCount = 0;
    foreach ($permissions as $perm) {
        $sql = "INSERT INTO permissions (permission_key, permission_name, description, category) 
                VALUES (:key, :name, :desc, :cat) 
                ON DUPLICATE KEY UPDATE permission_name = VALUES(permission_name), description = VALUES(description)";
        $db->execute($sql, [
            ':key' => $perm[0],
            ':name' => $perm[1],
            ':desc' => $perm[2],
            ':cat' => $perm[3]
        ]);
        $insertedCount++;
    }
    echo "<p style='color:green'>✅ Đã thêm/cập nhật $insertedCount permissions</p>";
    
    // Assign permissions to Manager role
    $managerPerms = [
        'users.view_team', 'users.create_user', 'users.edit_team',
        'reports.view_team', 'reports.view_personal', 'reports.export',
        'logs.view_team',
        'content.manage_assigned', 'content.view',
        'news.create', 'news.edit_own', 'news.publish',
        'registrations.view_assigned', 'registrations.edit', 'registrations.export',
        'cms.manage', 'cms.images', 'cms.texts',
        'content_blocks.view', 'content_blocks.manage',
        'profile.edit_own', 'profile.change_password'
    ];
    
    foreach ($managerPerms as $pKey) {
        $sql = "INSERT IGNORE INTO role_permissions (role, permission_key) VALUES ('manager', :pkey)";
        $db->execute($sql, [':pkey' => $pKey]);
    }
    echo "<p style='color:green'>✅ Đã gán quyền cho Manager</p>";
    
    // Assign permissions to User role
    $userPerms = [
        'reports.view_personal',
        'content.view',
        'news.edit_own',
        'registrations.view_assigned',
        'profile.edit_own', 'profile.change_password'
    ];
    
    foreach ($userPerms as $pKey) {
        $sql = "INSERT IGNORE INTO role_permissions (role, permission_key) VALUES ('user', :pkey)";
        $db->execute($sql, [':pkey' => $pKey]);
    }
    echo "<p style='color:green'>✅ Đã gán quyền cho User</p>";
    
    // Summary
    $totalPerms = $db->fetchColumn("SELECT COUNT(*) FROM permissions");
    $managerCount = $db->fetchColumn("SELECT COUNT(*) FROM role_permissions WHERE role = 'manager'");
    $userCount = $db->fetchColumn("SELECT COUNT(*) FROM role_permissions WHERE role = 'user'");
    
    echo "<hr>";
    echo "<h2>📊 Tổng kết</h2>";
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr><td>Tổng số permissions</td><td><strong>$totalPerms</strong></td></tr>";
    echo "<tr><td>Quyền của Manager</td><td><strong>$managerCount</strong></td></tr>";
    echo "<tr><td>Quyền của User</td><td><strong>$userCount</strong></td></tr>";
    echo "<tr><td>Quyền của Admin</td><td><strong>TẤT CẢ</strong></td></tr>";
    echo "</table>";
    
    echo "<br><p><strong>🎉 Hoàn tất!</strong> Bây giờ vào <a href='admin/dashboard.php#settings'>Settings > Phân quyền</a> để cấu hình.</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Lỗi: " . htmlspecialchars($e->getMessage()) . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
?>
