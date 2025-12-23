<?php
/**
 * Direct test of permission update logic
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once __DIR__ . '/admin/includes/auth_check.php';

header('Content-Type: text/html; charset=utf-8');

echo "<h2>Test Permission Update Handler</h2>";

echo "<h3>1. Check variables from auth_check.php:</h3>";
echo "<p>\$csrf: " . (isset($csrf) ? 'SET' : 'NOT SET') . "</p>";
echo "<p>\$userRole: " . (isset($userRole) ? htmlspecialchars($userRole) : 'NOT SET') . "</p>";
echo "<p>\$currentUser: " . (isset($currentUser) ? json_encode($currentUser) : 'NOT SET') . "</p>";

echo "<h3>2. Test Database class:</h3>";
try {
    $db = \App\Core\Database::getInstance();
    echo "<p style='color:green'>✓ Database::getInstance() works</p>";
    
    // Test INSERT
    $sql = "INSERT IGNORE INTO role_permissions (role, permission_key) VALUES (:role, :pkey)";
    $db->execute($sql, [':role' => 'manager', ':pkey' => 'test.permission']);
    echo "<p style='color:green'>✓ INSERT works</p>";
    
    // Test DELETE
    $sql = "DELETE FROM role_permissions WHERE role = :role AND permission_key = :pkey";
    $db->execute($sql, [':role' => 'manager', ':pkey' => 'test.permission']);
    echo "<p style='color:green'>✓ DELETE works</p>";
    
} catch (Exception $e) {
    echo "<p style='color:red'>❌ Error: " . $e->getMessage() . "</p>";
    echo "<pre>" . $e->getTraceAsString() . "</pre>";
}

echo "<h3>3. Test CSRF validation:</h3>";
$token = $csrf->getToken();
echo "<p>Token: " . htmlspecialchars($token) . "</p>";
$isValid = $csrf->validate($token);
echo "<p>Validation: " . ($isValid ? 'VALID' : 'INVALID') . "</p>";

echo "<h3 style='color:green'>All tests completed!</h3>";
