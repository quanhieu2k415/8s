<?php
/**
 * API Bootstrap
 * Include this at the top of API files for common setup
 */

require_once __DIR__ . '/../autoloader.php';

use App\Core\Response;
use App\Core\Logger;

// Set headers
Response::setCorsHeaders();
Response::handlePreflight();

// Set error handling
set_exception_handler(function (\Throwable $e) {
    $logger = Logger::getInstance();
    $logger->exception($e);
    
    Response::error(
        'Đã xảy ra lỗi server',
        'SERVER_ERROR',
        500
    );
});

// Get logger instance
$logger = Logger::getInstance();
