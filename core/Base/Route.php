<?php
namespace Core\Base;

use Exception;

class Route
{
    protected static array $routes = [];

    public static function get(string $route, callable | array $callback, array $middlewares = []): void
    {
        self::addRoute('GET', $route, $callback, $middlewares);
    }

    public static function post(string $route, callable | array $callback, array $middlewares = []): void
    {
        self::addRoute('POST', $route, $callback, $middlewares);
    }

    protected static function addRoute(string $method, string $route, callable | array $callback, array $middlewares): void
    {
        $params     = [];
        $routeParts = explode('/', $route);

        foreach ($routeParts as $key => $part) {
            if (strpos($part, ':') === 0) {
                $params[] = $key;
            }
        }

        $controller = null;

        if (! is_callable($callback) && is_array($callback)) {
            if (count($callback) !== 2) {
                throw new Exception("Controller callback must be an array with 2 elements (class, method).", 500);
            }
            [$controller, $callback] = $callback;
        }

        self::$routes[$route] = [
            'method'      => $method,
            'callback'    => $callback,
            'middlewares' => $middlewares,
            'controller'  => $controller,
            'params'      => $params,
        ];
    }

    public static function run(): void
    {
        $route  = strtok($_SERVER['REQUEST_URI'], '?');
        $method = $_SERVER['REQUEST_METHOD'];

        $matchedRoute = null;
        $routeParams  = [];

        foreach (self::$routes as $registeredRoute => $details) {
            $regex = self::convertToRegex($registeredRoute);
            if (preg_match($regex, $route, $matches)) {
                $matchedRoute = $details;
                $routeParams  = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
                break;
            }
        }

        if (! $matchedRoute) {
            throw new Exception("Route not found: $route", 404);
        }

        if ($method !== $matchedRoute['method']) {
            throw new Exception("Method not allowed for route: $route", 405);
        }
        foreach ($matchedRoute['middlewares'] as $middleware) {

            $middlewareInstance = new $middleware();
            if (is_object($middlewareInstance) && method_exists($middlewareInstance, 'guard') && is_callable([$middlewareInstance, 'guard'])) {
                $middlewareInstance->guard();
            }
        }

        try {
            if ($matchedRoute['controller']) {
                $controllerInstance = new $matchedRoute['controller']();
                call_user_func_array([$controllerInstance, $matchedRoute['callback']], array_values($routeParams));
            } else {
                call_user_func_array($matchedRoute['callback'], array_values($routeParams));
            }
        } catch (Exception $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    protected static function convertToRegex(string $route): string
    {
        $pattern = preg_replace_callback('/(:[a-zA-Z0-9_]+)/', function ($matches) {
            return '(?P<' . substr($matches[0], 1) . '>[^/]+)';
        }, $route);

        return '/^' . str_replace('/', '\/', $pattern) . '$/';
    }
}
