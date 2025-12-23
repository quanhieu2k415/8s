<?php
/**
 * Test permission API update directly
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/backend_api/bootstrap.php';

use App\Core\Database;
use App\Core\CSRF;

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Test Permission Update</h2>";

try {
    $db = Database::getInstance();
    
    echo "<h3>1. Test Database Connection:</h3>";
    echo "<p style='color:green'>✓ Database connected</p>";
    
    echo "<h3>2. Test INSERT IGNORE:</h3>";
    $sql = "INSERT IGNORE INTO role_permissions (role, permission_key) VALUES (:role, :permission_key)";
    $result = $db->execute($sql, [':role' => 'manager', ':permission_key' => 'logs.view_all']);
    echo "<p style='color:green'>✓ INSERT executed successfully</p>";
    
    echo "<h3>3. Test DELETE:</h3>";
    $sql = "DELETE FROM role_permissions WHERE role = :role AND permission_key = :permission_key";
    $result = $db->execute($sql, [':role' => 'manager', ':permission_key' => 'logs.view_all']);
    echo "<p style='color:green'>✓ DELETE executed successfully</p>";
    
    echo "<h3>4. Check role_permissions table structure:</h3>";
    $result = $db->fetchAll("DESCRIBE role_permissions");
    echo "<pre>";
    print_r($result);
    echo "</pre>";
    
    echo "<h3>5. Test CSRF class:</h3>";
    $csrf = new CSRF();
    echo "<p>CSRF Token: " . $csrf->getToken() . "</p>";
    echo "<p style='color:green'>✓ CSRF class works</p>";
    
    echo "<h3 style='color:green'>✓ All tests passed! Database operations work correctly.</h3>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}
