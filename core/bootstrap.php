<?php

use Core\Base\Route;

class Bootstrap
{
    protected $routes = [];
    protected $config = [];
    

    public function __construct($routes = [])
    {
        $this->routes = $routes;
    }
    public function helpersInit()
    {
        foreach (glob(__DIR__ . '/helpers/*.php') as $filename) {
            require_once $filename;
        }
    }
    public function routesInit()
    {
        foreach ($this->routes as $routeFile) {
            if (! file_exists($routeFile)) {
                throw new Exception("Route file not found: $routeFile");
            }
            require_once $routeFile;
        }
        Route::run();
    }
    protected function configInit()
    {
        foreach ($this->config as $configFile) {

            if (! file_exists($configFile)) {
                throw new Exception("Config file not found: $configFile");
            }
            require_once $configFile;
        }

    }
    protected function databaseInit()
    {

    }
    public function init()
    {
        try {
            $this->helpersInit();
            $this->configInit();
            $this->databaseInit();
            $this->routesInit();
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
