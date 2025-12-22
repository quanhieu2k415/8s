<?php
/**
 * Session Handler Class
 * Secure session management with timeout and fingerprinting
 * 
 * @package ICOGroup
 */

namespace App\Core;

use App\Config\Config;

class Session
{
    private static ?Session $instance = null;
    private Config $config;
    private bool $started = false;

    private function __construct()
    {
        $this->config = Config::getInstance();
    }

    /**
     * Get Session singleton instance
     */
    public static function getInstance(): Session
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Start session with secure settings
     */
    public function start(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $this->started = true;
            return;
        }

        $lifetime = (int) $this->config->get('SESSION_LIFETIME', 30) * 60;
        $secure = $this->config->get('SESSION_SECURE', 'false') === 'true';
        $sessionName = $this->config->get('SESSION_NAME', 'ICOSESSID');

        // Configure session settings
        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.cookie_httponly', '1');
        ini_set('session.cookie_samesite', 'Strict');
        ini_set('session.gc_maxlifetime', (string) $lifetime);

        session_name($sessionName);

        session_set_cookie_params([
            'lifetime' => $lifetime,
            'path' => '/',
            'domain' => '',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Strict'
        ]);

        session_start();
        $this->started = true;

        // Check session timeout
        $this->checkTimeout($lifetime);

        // Validate session fingerprint
        $this->validateFingerprint();
    }

    /**
     * Check if session has timed out
     */
    private function checkTimeout(int $lifetime): void
    {
        $lastActivity = $this->get('_last_activity');
        
        if ($lastActivity !== null) {
            if (time() - $lastActivity > $lifetime) {
                $this->destroy();
                $this->start();
            }
        }
        
        $this->set('_last_activity', time());
    }

    /**
     * Validate session fingerprint to prevent hijacking
     */
    private function validateFingerprint(): void
    {
        $fingerprint = $this->generateFingerprint();
        $storedFingerprint = $this->get('_fingerprint');

        if ($storedFingerprint === null) {
            $this->set('_fingerprint', $fingerprint);
        } elseif ($storedFingerprint !== $fingerprint) {
            // Possible session hijacking attempt
            $this->destroy();
            $this->start();
        }
    }

    /**
     * Generate session fingerprint based on user agent and IP
     */
    private function generateFingerprint(): string
    {
        $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
        $ip = $_SERVER['REMOTE_ADDR'] ?? '';
        $secret = $this->config->getAppSecret();
        
        return hash('sha256', $userAgent . $ip . $secret);
    }

    /**
     * Regenerate session ID (call after login)
     */
    public function regenerate(): void
    {
        if ($this->started) {
            session_regenerate_id(true);
        }
    }

    /**
     * Get session value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        return $_SESSION[$key] ?? $default;
    }

    /**
     * Set session value
     */
    public function set(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION[$key] = $value;
    }

    /**
     * Check if session key exists
     */
    public function has(string $key): bool
    {
        $this->ensureStarted();
        return isset($_SESSION[$key]);
    }

    /**
     * Remove session key
     */
    public function remove(string $key): void
    {
        $this->ensureStarted();
        unset($_SESSION[$key]);
    }

    /**
     * Get all session data
     */
    public function all(): array
    {
        $this->ensureStarted();
        return $_SESSION;
    }

    /**
     * Clear all session data
     */
    public function clear(): void
    {
        $this->ensureStarted();
        $_SESSION = [];
    }

    /**
     * Destroy session completely
     */
    public function destroy(): void
    {
        if ($this->started || session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION = [];
            
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000,
                    $params['path'],
                    $params['domain'],
                    $params['secure'],
                    $params['httponly']
                );
            }
            
            session_destroy();
            $this->started = false;
        }
    }

    /**
     * Flash message - store for next request only
     */
    public function flash(string $key, mixed $value): void
    {
        $this->ensureStarted();
        $_SESSION['_flash'][$key] = $value;
    }

    /**
     * Get flash message and remove it
     */
    public function getFlash(string $key, mixed $default = null): mixed
    {
        $this->ensureStarted();
        $value = $_SESSION['_flash'][$key] ?? $default;
        unset($_SESSION['_flash'][$key]);
        return $value;
    }

    /**
     * Check if user is authenticated
     */
    public function isAuthenticated(): bool
    {
        return $this->get('admin_user_id') !== null;
    }

    /**
     * Get authenticated user ID
     */
    public function getUserId(): ?int
    {
        return $this->get('admin_user_id');
    }

    /**
     * Get authenticated user data
     */
    public function getUser(): ?array
    {
        return $this->get('admin_user');
    }

    /**
     * Set authenticated user
     */
    public function setUser(array $user): void
    {
        $this->regenerate();
        $this->set('admin_user_id', $user['id']);
        $this->set('admin_user', $user);
        $this->set('_login_time', time());
    }

    /**
     * Ensure session is started
     */
    private function ensureStarted(): void
    {
        if (!$this->started) {
            $this->start();
        }
    }
}
