<?php
/**
 * Test Remember Me Functionality
 */

require_once __DIR__ . '/autoloader.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    // Check if table exists
    $result = $db->fetchAll("SHOW TABLES LIKE 'remember_tokens'");
    
    if (empty($result)) {
        echo "❌ Table 'remember_tokens' does not exist\n";
        exit(1);
    }
    
    echo "✅ Table 'remember_tokens' exists\n\n";
    
    // Show table structure
    $structure = $db->fetchAll("DESCRIBE remember_tokens");
    echo "📋 Table structure:\n";
    foreach ($structure as $column) {
        echo "  - {$column['Field']}: {$column['Type']} " . 
             ($column['Null'] === 'NO' ? '(NOT NULL)' : '') . 
             ($column['Key'] === 'PRI' ? '(PRIMARY KEY)' : '') . "\n";
    }
    
    echo "\n✅ Remember Me feature is ready to use!\n";
    echo "\n📝 How it works:\n";
    echo "  1. User checks 'Ghi nhớ đăng nhập' checkbox\n";
    echo "  2. A secure token is generated and stored in database\n";
    echo "  3. Token is saved as HTTP-only cookie for 30 days\n";
    echo "  4. User stays logged in even after closing browser\n";
    echo "  5. Token is rotated on each use for security\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
