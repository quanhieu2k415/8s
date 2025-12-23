<?php
/**
 * Test Activity Logs API directly
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/admin/includes/auth_check.php';

use App\Core\Database;

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Test Activity Logs</h2>";

echo "<h3>1. Check activity_logs table:</h3>";
try {
    $db = Database::getInstance();
    
    // Check if table exists
    $result = $db->fetchAll("SHOW TABLES LIKE 'activity_logs'");
    if (empty($result)) {
        echo "<p style='color:red'>❌ Bảng activity_logs KHÔNG tồn tại!</p>";
        echo "<p>Bạn cần chạy file: <code>backend_api/database/permission_migration.sql</code></p>";
    } else {
        echo "<p style='color:green'>✓ Bảng activity_logs tồn tại</p>";
        
        // Count records
        $count = $db->fetchAll("SELECT COUNT(*) as total FROM activity_logs");
        echo "<p>Số lượng records: <strong>" . ($count[0]['total'] ?? 0) . "</strong></p>";
        
        if ($count[0]['total'] == 0) {
            echo "<p style='color:orange'>⚠️ Bảng trống! Chưa có logs nào.</p>";
            
            // Insert a test log
            echo "<h4>Tạo log test:</h4>";
            $sql = "INSERT INTO activity_logs (user_id, username, role, action, details, ip_address) 
                    VALUES (:uid, :uname, :role, :action, :details, :ip)";
            $db->execute($sql, [
                ':uid' => $currentUser['id'] ?? 1,
                ':uname' => $currentUser['username'] ?? 'admin',
                ':role' => $userRole ?? 'admin',
                ':action' => 'login',
                ':details' => 'Test login log',
                ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
            ]);
            echo "<p style='color:green'>✓ Đã tạo 1 log test</p>";
        }
        
        // Show sample logs
        echo "<h4>Sample logs (5 gần nhất):</h4>";
        $logs = $db->fetchAll("SELECT * FROM activity_logs ORDER BY created_at DESC LIMIT 5");
        if (!empty($logs)) {
            echo "<table border='1' cellpadding='5'>";
            echo "<tr><th>ID</th><th>User</th><th>Role</th><th>Action</th><th>Details</th><th>Time</th></tr>";
            foreach ($logs as $log) {
                echo "<tr>";
                echo "<td>" . $log['id'] . "</td>";
                echo "<td>" . htmlspecialchars($log['username'] ?? 'N/A') . "</td>";
                echo "<td>" . htmlspecialchars($log['role'] ?? '') . "</td>";
                echo "<td>" . htmlspecialchars($log['action']) . "</td>";
                echo "<td>" . htmlspecialchars($log['details'] ?? '') . "</td>";
                echo "<td>" . $log['created_at'] . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
}

echo "<h3>2. Test API Response:</h3>";
echo "<p>API URL: <a href='backend_api/activity_logs_api.php?action=list' target='_blank'>backend_api/activity_logs_api.php?action=list</a></p>";
