<?php
/**
 * Session Check API
 * Returns session status and time remaining
 */

require_once __DIR__ . '/../autoloader.php';

use App\Services\Auth;
use App\Core\Response;
use App\Core\Session;

Response::setCorsHeaders();

$auth = Auth::getInstance();
$session = Session::getInstance();

// Check if authenticated
if (!$auth->check()) {
    Response::json([
        'authenticated' => false,
        'message' => 'Session đã hết hạn'
    ]);
    exit;
}

// Get session info
$lastActivity = $session->get('_last_activity');
$lifetime = 30 * 60; // 30 minutes in seconds

if ($lastActivity === null) {
    $lastActivity = time();
    $session->set('_last_activity', $lastActivity);
}

$timeRemaining = $lifetime - (time() - $lastActivity);

// If time remaining is negative, session has expired
if ($timeRemaining <= 0) {
    $auth->logout();
    Response::json([
        'authenticated' => false,
        'message' => 'Session đã hết hạn'
    ]);
    exit;
}

Response::json([
    'authenticated' => true,
    'timeRemaining' => $timeRemaining,
    'warningThreshold' => 5 * 60, // 5 minutes
    'lastActivity' => $lastActivity,
    'currentTime' => time()
]);
