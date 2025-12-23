<?php
/**
 * Debug script to test permissions
 */

require_once __DIR__ . '/backend_api/bootstrap.php';

use App\Core\Database;

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Debug Permissions</h2>";

try {
    $db = Database::getInstance();
    
    // Test if permissions table exists
    echo "<h3>1. Kiểm tra bảng permissions:</h3>";
    try {
        $result = $db->fetchAll("SHOW TABLES LIKE 'permissions'");
        if (empty($result)) {
            echo "<p style='color:red'>❌ Bảng 'permissions' CHƯA TỒN TẠI</p>";
            echo "<p>→ Bạn cần import file: <code>backend_api/database/permission_migration.sql</code></p>";
        } else {
            echo "<p style='color:green'>✓ Bảng 'permissions' tồn tại</p>";
            
            // Count permissions
            $count = $db->fetchAll("SELECT COUNT(*) as cnt FROM permissions");
            echo "<p>→ Số lượng permissions: " . ($count[0]['cnt'] ?? 0) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Lỗi: " . $e->getMessage() . "</p>";
    }
    
    // Test if role_permissions table exists
    echo "<h3>2. Kiểm tra bảng role_permissions:</h3>";
    try {
        $result = $db->fetchAll("SHOW TABLES LIKE 'role_permissions'");
        if (empty($result)) {
            echo "<p style='color:red'>❌ Bảng 'role_permissions' CHƯA TỒN TẠI</p>";
        } else {
            echo "<p style='color:green'>✓ Bảng 'role_permissions' tồn tại</p>";
            
            // Count role permissions
            $count = $db->fetchAll("SELECT COUNT(*) as cnt FROM role_permissions");
            echo "<p>→ Số lượng role_permissions: " . ($count[0]['cnt'] ?? 0) . "</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>❌ Lỗi: " . $e->getMessage() . "</p>";
    }
    
    echo "<h3>3. Hướng dẫn sửa lỗi:</h3>";
    echo "<ol>";
    echo "<li>Mở <a href='http://localhost/phpmyadmin' target='_blank'>phpMyAdmin</a></li>";
    echo "<li>Chọn database <strong>icogroup_db</strong></li>";
    echo "<li>Click tab <strong>Import</strong></li>";
    echo "<li>Chọn file: <code>c:\\xampp\\htdocs\\web8s\\backend_api\\database\\permission_migration.sql</code></li>";
    echo "<li>Click <strong>Import</strong></li>";
    echo "</ol>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>Lỗi kết nối database: " . $e->getMessage() . "</p>";
}
