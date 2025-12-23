<?php
/**
 * Session Activity Update API
 * Updates last activity timestamp to extend session
 */

require_once __DIR__ . '/../autoloader.php';

use App\Services\Auth;
use App\Core\Response;
use App\Core\Session;

Response::setCorsHeaders();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    Response::error('Method not allowed', 'METHOD_ERROR', 405);
}

$auth = Auth::getInstance();
$session = Session::getInstance();

// Check if authenticated
if (!$auth->check()) {
    Response::error('Not authenticated', 'AUTH_ERROR', 401);
}

// Update last activity timestamp
$session->set('_last_activity', time());

Response::json([
    'success' => true,
    'message' => 'Activity updated',
    'data' => ['lastActivity' => time()]
]);
