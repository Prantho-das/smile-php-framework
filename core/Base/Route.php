<?php
namespace Core\Base;

use Exception;

class Route
{
    protected static $routes = [];

    public static function get($route, $callback, $middlewares = [])
    {
        // Validate that middlewares is an array
        if (! is_array($middlewares)) {
            throw new Exception("Middlewares must be an array", 500);
        }

        $controller  = null;
        $params      = [];
        $route_parts = explode('/', $route);
        foreach ($route_parts as $key => $part) {
            if (strpos($part, ':') !== false) {
                 $params[] = $key;
            }
        }
        // Validate and process the callback
        if (! is_callable($callback) && is_array($callback)) {
            if (count($callback) !== 2) {
                throw new Exception("Controller must be an array with 2 elements", 500);
            }
            [$controller, $callback] = $callback; // Destructure array for better readability
        }

        // Add the route to the routes array
        self::$routes[$route] = [
            'method'      => 'GET',
            'callback'    => $callback,
            'middlewares' => $middlewares,
            'controller'  => $controller,
            'params'      => $params,
        ];
    }

    public static function post($route, $callback, $middlewares = [])
    {
        // Validate that middlewares is an array
        if (! is_array($middlewares)) {
            throw new Exception("Middlewares must be an array", 500);
        }

        $controller = null;
        $params     = [];

        // Validate and process the callback
        if (! is_callable($callback) && is_array($callback)) {
            if (count($callback) !== 2) {
                throw new Exception("Controller must be an array with 2 elements", 500);
            }
            [$controller, $callback] = $callback; // Destructure array for better readability
        }

        // Add the route to the routes array
        self::$routes[$route] = [
            'method'      => 'POST',
            'callback'    => $callback,
            'middlewares' => $middlewares,
            'controller'  => $controller,
            'params'      => $params,

        ];
    }

    public static function run()
    {
        $route  = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        
        if (strpos($route, '?') !== false) {
            $route = explode('?', $route)[0] ?? '/';
        }
        dd(self::$routes, $route,$_SERVER, $method);
        if (! array_key_exists($route, self::$routes)) {
            throw new \Exception("Route not found: $route", 404);
        }
        if ($method !== self::$routes[$route]['method']) {
            throw new \Exception("Method not allowed: $route", 500);
        }
  
        try {
            if (self::$routes[$route]['controller']) {
                $controllerInstance = new self::$routes[$route]['controller'];
                return call_user_func_array([$controllerInstance, self::$routes[$route]['callback']], []);
            }
            return call_user_func(self::$routes[$route]['callback']);
        } catch (\Throwable $th) {
            throw new \Exception($th->getMessage(), 500);
        }

    }
}
