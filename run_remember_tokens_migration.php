<?php
/**
 * Run Remember Tokens Migration
 */

require_once __DIR__ . '/autoloader.php';

use App\Core\Database;

try {
    $db = Database::getInstance();
    
    $sql = file_get_contents(__DIR__ . '/backend_api/database/remember_tokens_migration.sql');
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement)) {
            $db->execute($statement);
        }
    }
    
    echo "✅ Migration successful: remember_tokens table created\n";
    
    // Verify table exists (MySQL syntax)
    $result = $db->fetchAll("SHOW TABLES LIKE 'remember_tokens'");
    
    if (!empty($result)) {
        echo "✅ Table 'remember_tokens' verified in database\n";
        
        // Show table structure
        $structure = $db->fetchAll("DESCRIBE remember_tokens");
        echo "\n📋 Table structure:\n";
        foreach ($structure as $column) {
            echo "  - {$column['Field']}: {$column['Type']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
