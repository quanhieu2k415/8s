<?php
/**
 * Admin Logout
 */

require_once __DIR__ . '/../autoloader.php';

use App\Services\Auth;

$auth = Auth::getInstance();
$auth->logout();

header('Location: index.php');
exit;
