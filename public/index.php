<?php

// Front Controller - Entry Point

// Require Manual Autoloader
require_once __DIR__ . '/../autoload.php';

use App\Core\Router;

// No need for Dotenv anymore, config is loaded in Database class or Controllers as needed

// Initialize Router
$router = new Router();

// --- Define Routes ---
// Home
$router->get('/', [App\Controllers\HomeController::class, 'index']);

// Example: Test Database Connection
$router->get('/test-db', function() {
    try {
        $db = App\Core\Database::getInstance()->getConnection();
        echo "Database connection successful!";
    } catch (Exception $e) {
        echo "Database error: " . $e->getMessage();
    }
});

// Dispatch
$router->resolve();
