<?php
/**
 * CSRF Protection Class
 * Generate and validate CSRF tokens
 * 
 * @package ICOGroup
 */

namespace App\Core;

use App\Config\Config;

class CSRF
{
    private const TOKEN_NAME = '_csrf_token';
    private const TOKEN_TIME = '_csrf_time';

    private Session $session;
    private Config $config;

    public function __construct()
    {
        $this->session = Session::getInstance();
        $this->config = Config::getInstance();
    }

    /**
     * Generate new CSRF token
     */
    public function generateToken(): string
    {
        $token = bin2hex(random_bytes(32));
        
        $this->session->set(self::TOKEN_NAME, $token);
        $this->session->set(self::TOKEN_TIME, time());
        
        return $token;
    }

    /**
     * Get current token or generate new one
     */
    public function getToken(): string
    {
        $token = $this->session->get(self::TOKEN_NAME);
        $time = $this->session->get(self::TOKEN_TIME);
        
        $lifetime = (int) $this->config->get('CSRF_TOKEN_LIFETIME', 3600);
        
        // Generate new token if expired or doesn't exist
        if ($token === null || $time === null || (time() - $time) > $lifetime) {
            return $this->generateToken();
        }
        
        return $token;
    }

    /**
     * Validate CSRF token
     */
    public function validateToken(?string $token): bool
    {
        if ($token === null || $token === '') {
            return false;
        }

        $storedToken = $this->session->get(self::TOKEN_NAME);
        $storedTime = $this->session->get(self::TOKEN_TIME);

        if ($storedToken === null || $storedTime === null) {
            return false;
        }

        // Check if token has expired
        $lifetime = (int) $this->config->get('CSRF_TOKEN_LIFETIME', 3600);
        if ((time() - $storedTime) > $lifetime) {
            return false;
        }

        // Timing-safe comparison
        return hash_equals($storedToken, $token);
    }

    /**
     * Validate and rotate token (use after validation)
     */
    public function validateAndRotate(?string $token): bool
    {
        $isValid = $this->validateToken($token);
        
        if ($isValid) {
            // Generate new token after successful validation
            $this->generateToken();
        }
        
        return $isValid;
    }

    /**
     * Get HTML input field for forms
     */
    public function getHiddenInput(): string
    {
        $token = $this->getToken();
        return sprintf(
            '<input type="hidden" name="csrf_token" value="%s">',
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Get meta tag for AJAX requests
     */
    public function getMetaTag(): string
    {
        $token = $this->getToken();
        return sprintf(
            '<meta name="csrf-token" content="%s">',
            htmlspecialchars($token, ENT_QUOTES, 'UTF-8')
        );
    }

    /**
     * Extract token from request
     */
    public function getTokenFromRequest(): ?string
    {
        // Check POST data
        if (isset($_POST['csrf_token'])) {
            return $_POST['csrf_token'];
        }

        // Check headers (for AJAX)
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (substr($key, 0, 5) === 'HTTP_') {
                $header = str_replace(' ', '-', ucwords(str_replace('_', ' ', strtolower(substr($key, 5)))));
                $headers[$header] = $value;
            }
        }

        if (isset($headers['X-Csrf-Token'])) {
            return $headers['X-Csrf-Token'];
        }

        if (isset($headers['X-Xsrf-Token'])) {
            return $headers['X-Xsrf-Token'];
        }

        return null;
    }

    /**
     * Middleware-style validation
     * Returns true if valid, throws exception if not
     */
    public function verify(): bool
    {
        $method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        
        // Only validate for state-changing methods
        if (!in_array($method, ['POST', 'PUT', 'PATCH', 'DELETE'])) {
            return true;
        }

        $token = $this->getTokenFromRequest();
        
        if (!$this->validateToken($token)) {
            http_response_code(403);
            throw new \Exception('CSRF token validation failed');
        }

        return true;
    }
}
