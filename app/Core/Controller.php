<?php

namespace App\Core;

class Controller
{
    public function view($view, $data = [])
    {
        // Extract data to variables
        extract($data);

        // Check if view exists
        $viewPath = __DIR__ . '/../Views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            require_once $viewPath;
        } else {
            die("View $view not found!");
        }
    }

    public function json($data, $status = 200)
    {
        header('Content-Type: application/json');
        http_response_code($status);
        echo json_encode($data);
    }
}
