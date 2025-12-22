<?php
/**
 * Response Class
 * Standardized API responses
 * 
 * @package ICOGroup
 */

namespace App\Core;

class Response
{
    /**
     * Send JSON success response
     */
    public static function success(mixed $data = null, string $message = 'Success', int $statusCode = 200): void
    {
        self::json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ], $statusCode);
    }

    /**
     * Send JSON error response
     */
    public static function error(
        string $message,
        string $errorCode = 'ERROR',
        int $statusCode = 400,
        array $details = []
    ): void {
        $response = [
            'success' => false,
            'error' => [
                'code' => $errorCode,
                'message' => $message
            ]
        ];

        if (!empty($details)) {
            $response['error']['details'] = $details;
        }

        self::json($response, $statusCode);
    }

    /**
     * Send JSON response
     */
    public static function json(mixed $data, int $statusCode = 200): void
    {
        http_response_code($statusCode);
        header('Content-Type: application/json; charset=utf-8');
        header('X-Content-Type-Options: nosniff');
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    /**
     * Send paginated response
     */
    public static function paginated(
        array $data,
        int $total,
        int $page,
        int $limit,
        string $message = 'Success'
    ): void {
        $totalPages = (int) ceil($total / $limit);
        
        self::json([
            'success' => true,
            'data' => $data,
            'message' => $message,
            'meta' => [
                'total' => $total,
                'count' => count($data),
                'page' => $page,
                'limit' => $limit,
                'total_pages' => $totalPages,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ]);
    }

    /**
     * Send validation error response
     */
    public static function validationError(array $errors): void
    {
        self::error(
            'Dữ liệu không hợp lệ',
            'VALIDATION_ERROR',
            422,
            ['fields' => $errors]
        );
    }

    /**
     * Send unauthorized response
     */
    public static function unauthorized(string $message = 'Unauthorized'): void
    {
        self::error($message, 'UNAUTHORIZED', 401);
    }

    /**
     * Send forbidden response
     */
    public static function forbidden(string $message = 'Forbidden'): void
    {
        self::error($message, 'FORBIDDEN', 403);
    }

    /**
     * Send not found response
     */
    public static function notFound(string $message = 'Resource not found'): void
    {
        self::error($message, 'NOT_FOUND', 404);
    }

    /**
     * Send server error response
     */
    public static function serverError(string $message = 'Internal server error'): void
    {
        self::error($message, 'SERVER_ERROR', 500);
    }

    /**
     * Send created response
     */
    public static function created(mixed $data = null, string $message = 'Created successfully'): void
    {
        self::success($data, $message, 201);
    }

    /**
     * Send no content response
     */
    public static function noContent(): void
    {
        http_response_code(204);
        exit;
    }

    /**
     * Redirect to URL
     */
    public static function redirect(string $url, int $statusCode = 302): void
    {
        http_response_code($statusCode);
        header("Location: $url");
        exit;
    }

    /**
     * Set security headers
     */
    public static function setSecurityHeaders(): void
    {
        header("X-Content-Type-Options: nosniff");
        header("X-Frame-Options: SAMEORIGIN");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
        
        // CSP - adjust as needed for your application
        header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https:; style-src 'self' 'unsafe-inline' https:; img-src 'self' data: https:; font-src 'self' https:;");
    }

    /**
     * Set CORS headers
     */
    public static function setCorsHeaders(string $origin = '*'): void
    {
        header("Access-Control-Allow-Origin: $origin");
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Csrf-Token");
        header("Access-Control-Max-Age: 86400");
    }

    /**
     * Handle preflight OPTIONS request
     */
    public static function handlePreflight(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            self::setCorsHeaders();
            http_response_code(200);
            exit;
        }
    }
}
