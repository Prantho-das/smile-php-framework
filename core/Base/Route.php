<?php
namespace Core\Base;

class Route
{
    protected static $routes = [];

    public static function get($route, $callback, $middlewares = [])
    {
        self::$routes[$route] = [
            'method'      => 'GET',
            'callback'    => $callback,
            'middlewares' => $middlewares,
        ];
    }
    public static function post($route, $callback, $middlewares = [])
    {
        self::$routes[$route] = [
            'method'      => 'POST',
            'callback'    => $callback,
            'middlewares' => $middlewares,
        ];

    }

    public static function run()
    {
        $route = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];
        if (! array_key_exists($route, self::$routes)) {
            throw new \Exception("Route not found: $route");
        }
        if($method !== self::$routes[$route]['method']) {
            throw new \Exception("Method not allowed: $route");
        }
         dd($_SERVER,self::$routes);
        return call_user_func(self::$routes[$route]['callback']);
    }
}
