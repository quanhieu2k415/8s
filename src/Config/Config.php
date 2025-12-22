<?php
/**
 * Configuration Class
 * Loads and provides access to application configuration
 * 
 * @package ICOGroup
 */

namespace App\Config;

class Config
{
    private static ?Config $instance = null;
    private array $config = [];

    private function __construct()
    {
        $this->loadEnv();
        $this->setDefaults();
    }

    /**
     * Get Config singleton instance
     */
    public static function getInstance(): Config
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load environment variables from .env file
     */
    private function loadEnv(): void
    {
        $envPath = dirname(__DIR__, 2) . '/.env';
        
        if (!file_exists($envPath)) {
            return;
        }

        $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        foreach ($lines as $line) {
            // Skip comments
            if (strpos(trim($line), '#') === 0) {
                continue;
            }

            if (strpos($line, '=') !== false) {
                list($key, $value) = explode('=', $line, 2);
                $key = trim($key);
                $value = trim($value);
                
                // Remove quotes if present
                $value = trim($value, '"\'');
                
                $this->config[$key] = $value;
                
                // Also set as environment variable
                if (!getenv($key)) {
                    putenv("$key=$value");
                }
            }
        }
    }

    /**
     * Set default configuration values
     */
    private function setDefaults(): void
    {
        $defaults = [
            // Database
            'DB_HOST' => 'localhost',
            'DB_NAME' => 'db_nhanluc',
            'DB_USER' => 'root',
            'DB_PASS' => '',
            'DB_CHARSET' => 'utf8mb4',
            
            // Application
            'APP_ENV' => 'local',
            'APP_DEBUG' => 'true',
            'APP_URL' => 'http://localhost/web8s',
            'APP_SECRET' => 'change-this-to-a-random-secret-key-32chars',
            
            // Session
            'SESSION_LIFETIME' => '30', // minutes
            'SESSION_SECURE' => 'false',
            'SESSION_NAME' => 'ICOSESSID',
            
            // File uploads
            'UPLOAD_MAX_SIZE' => '5242880', // 5MB
            'UPLOAD_PATH' => 'storage/uploads',
            'UPLOAD_ALLOWED_TYPES' => 'jpg,jpeg,png,gif,webp',
            
            // Logging
            'LOG_PATH' => 'storage/logs',
            'LOG_LEVEL' => 'debug',
            
            // Security
            'CSRF_TOKEN_LIFETIME' => '3600', // 1 hour
            'LOGIN_MAX_ATTEMPTS' => '5',
            'LOGIN_LOCKOUT_TIME' => '900', // 15 minutes
        ];

        foreach ($defaults as $key => $value) {
            if (!isset($this->config[$key])) {
                $this->config[$key] = $value;
            }
        }
    }

    /**
     * Get configuration value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Get all database configuration
     */
    public function getDatabase(): array
    {
        return [
            'host' => $this->get('DB_HOST'),
            'name' => $this->get('DB_NAME'),
            'user' => $this->get('DB_USER'),
            'pass' => $this->get('DB_PASS'),
            'charset' => $this->get('DB_CHARSET'),
        ];
    }

    /**
     * Check if application is in debug mode
     */
    public function isDebug(): bool
    {
        return $this->get('APP_DEBUG') === 'true';
    }

    /**
     * Check if application is in production
     */
    public function isProduction(): bool
    {
        return $this->get('APP_ENV') === 'production';
    }

    /**
     * Get application URL
     */
    public function getAppUrl(): string
    {
        return rtrim($this->get('APP_URL'), '/');
    }

    /**
     * Get application secret key
     */
    public function getAppSecret(): string
    {
        return $this->get('APP_SECRET');
    }
}
