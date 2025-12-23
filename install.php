<?php
/**
 * Installation Script
 * Run this once to set up the database and create default admin user
 * 
 * Usage: php install.php
 * Or access via browser: http://localhost/web8s/install.php
 * 
 * IMPORTANT: Delete this file after installation!
 */

// Prevent running in production
if (php_sapi_name() !== 'cli' && !isset($_GET['confirm'])) {
    echo "<h1>ICOGroup Installation</h1>";
    echo "<p>This script will:</p>";
    echo "<ul>";
    echo "<li>Create necessary database tables (admin_users, remember_tokens, audit_logs)</li>";
    echo "<li>Add indexes to existing tables</li>";
    echo "<li>Create default admin user</li>";
    echo "</ul>";
    echo "<p><strong>Username:</strong> admin<br><strong>Password:</strong> cris123</p>";
    echo "<p><a href='?confirm=1' style='padding: 10px 20px; background: #10B981; color: white; text-decoration: none; border-radius: 5px;'>Run Installation</a></p>";
    echo "<p style='color: red;'><strong>Warning:</strong> Delete this file after installation!</p>";
    exit;
}

require_once __DIR__ . '/autoloader.php';

use App\Config\Config;
use App\Core\Database;
use App\Repositories\AdminUserRepository;

echo "Starting ICOGroup Installation...\n\n";

try {
    $config = Config::getInstance();
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    echo "✓ Database connection successful\n";
    
    // Run migration
    echo "\nRunning database migration...\n";
    
    // Create admin_users table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS admin_users (
            id INT AUTO_INCREMENT PRIMARY KEY,
            username VARCHAR(50) NOT NULL UNIQUE,
            email VARCHAR(255) DEFAULT NULL,
            password_hash VARCHAR(255) NOT NULL,
            role ENUM('admin', 'manager', 'user') NOT NULL DEFAULT 'user',
            department VARCHAR(100) DEFAULT NULL,
            manager_id INT DEFAULT NULL,
            is_active TINYINT(1) NOT NULL DEFAULT 1,
            last_login TIMESTAMP NULL DEFAULT NULL,
            login_attempts INT NOT NULL DEFAULT 0,
            locked_until TIMESTAMP NULL DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            profile_updated_at TIMESTAMP NULL DEFAULT NULL,
            INDEX idx_username (username),
            INDEX idx_email (email),
            INDEX idx_role (role),
            INDEX idx_is_active (is_active),
            INDEX idx_manager_id (manager_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Created admin_users table\n";
    
    // Create remember_tokens table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS remember_tokens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            token_hash VARCHAR(255) NOT NULL,
            expires_at TIMESTAMP NOT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_token_hash (token_hash),
            INDEX idx_expires_at (expires_at),
            CONSTRAINT fk_remember_user FOREIGN KEY (user_id) 
                REFERENCES admin_users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Created remember_tokens table\n";
    
    // Create audit_logs table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS audit_logs (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT DEFAULT NULL,
            action VARCHAR(50) NOT NULL,
            entity_type VARCHAR(50) DEFAULT NULL,
            entity_id INT DEFAULT NULL,
            old_values JSON DEFAULT NULL,
            new_values JSON DEFAULT NULL,
            ip_address VARCHAR(45) DEFAULT NULL,
            user_agent TEXT DEFAULT NULL,
            created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_user_id (user_id),
            INDEX idx_action (action),
            INDEX idx_entity (entity_type, entity_id),
            INDEX idx_created_at (created_at)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");
    echo "✓ Created audit_logs table\n";
    
    // Add indexes to user table (registrations)
    try {
        // Check if deleted_at column exists
        $result = $pdo->query("SHOW COLUMNS FROM user LIKE 'deleted_at'");
        if ($result->rowCount() === 0) {
            $pdo->exec("ALTER TABLE user ADD COLUMN deleted_at TIMESTAMP NULL DEFAULT NULL");
            echo "✓ Added deleted_at column to user table\n";
        }
        
        // Try to add indexes (will fail silently if they exist)
        @$pdo->exec("CREATE INDEX idx_user_ngay_nhan ON user(ngay_nhan)");
        @$pdo->exec("CREATE INDEX idx_user_chuong_trinh ON user(chuong_trinh)");
        @$pdo->exec("CREATE INDEX idx_user_quoc_gia ON user(quoc_gia)");
        echo "✓ Added indexes to user table\n";
    } catch (Exception $e) {
        echo "! User table modifications skipped (may not exist yet)\n";
    }
    
    // Add indexes to news table
    try {
        @$pdo->exec("CREATE INDEX idx_news_created_at ON news(created_at)");
        @$pdo->exec("CREATE INDEX idx_news_category ON news(category)");
        @$pdo->exec("CREATE INDEX idx_news_status ON news(status)");
        echo "✓ Added indexes to news table\n";
    } catch (Exception $e) {
        echo "! News table modifications skipped (may not exist yet)\n";
    }
    
    // Create default admin user
    echo "\nCreating default admin user...\n";
    
    $userRepo = new AdminUserRepository();
    
    // Check if admin already exists
    $existingAdmin = $userRepo->findByUsername('admin');
    
    if ($existingAdmin) {
        // Update password
        $userRepo->updatePassword($existingAdmin['id'], 'cris123');
        echo "✓ Updated admin password\n";
    } else {
        // Create new admin
        $userId = $userRepo->createUser('admin', 'cris123', 'admin@icogroup.vn', 'admin');
        echo "✓ Created admin user (ID: {$userId})\n";
    }
    
    // Create storage directories
    echo "\nCreating storage directories...\n";
    
    $dirs = [
        __DIR__ . '/storage',
        __DIR__ . '/storage/logs',
        __DIR__ . '/storage/cache',
        __DIR__ . '/storage/uploads',
    ];
    
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            echo "✓ Created: " . basename($dir) . "\n";
        }
    }
    
    // Create .htaccess for storage protection
    $htaccess = __DIR__ . '/storage/.htaccess';
    if (!file_exists($htaccess)) {
        file_put_contents($htaccess, "Deny from all\n");
        echo "✓ Created storage/.htaccess\n";
    }
    
    echo "\n" . str_repeat("=", 50) . "\n";
    echo "✓ Installation completed successfully!\n";
    echo str_repeat("=", 50) . "\n";
    echo "\nDefault Admin Credentials:\n";
    echo "  Username: admin\n";
    echo "  Password: cris123\n";
    echo "\nAdmin Panel: " . $config->getAppUrl() . "/admin/\n";
    echo "\n⚠️  IMPORTANT: Delete this install.php file now!\n";
    
} catch (Exception $e) {
    echo "\n✗ Installation failed!\n";
    echo "Error: " . $e->getMessage() . "\n";
    
    if ($config && $config->isDebug()) {
        echo "\nStack trace:\n" . $e->getTraceAsString() . "\n";
    }
    
    exit(1);
}
