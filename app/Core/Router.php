<?php

namespace App\Core;

class Router
{
    private $routes = [];

    public function get($path, $callback)
    {
        $this->routes['GET'][$path] = $callback;
    }

    public function post($path, $callback)
    {
        $this->routes['POST'][$path] = $callback;
    }

    public function resolve()
    {
        $path = $this->getPath();
        $method = $_SERVER['REQUEST_METHOD'];

        $callback = $this->routes[$method][$path] ?? false;

        if ($callback === false) {
            // 404 handling
            http_response_code(404);
            echo "404 - Not Found";
            return;
        }

        if (is_array($callback)) {
            $controller = new $callback[0]();
            $action = $callback[1];
            
            // Invoke controller action
            call_user_func([$controller, $action]);
        } else {
            // Callable function
            call_user_func($callback);
        }
    }

    private function getPath()
    {
        $path = $_SERVER['REQUEST_URI'] ?? '/';
        $position = strpos($path, '?');
        
        if ($position === false) {
            return $path;
        }
        
        return substr($path, 0, $position);
    }
}
