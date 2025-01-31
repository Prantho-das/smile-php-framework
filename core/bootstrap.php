<?php

use Core\Base\Db;
use Core\Base\Route;
use Core\Base\Session;
use Core\Contracts\BaseAppContract;

define('ROOT', dirname(__DIR__));
define('VIEWS', ROOT . '/views/');

class Bootstrap extends BaseAppContract
{
    protected $routes     = [];
    protected $configPath = [];
    protected $config     = [];


    public function __construct($routes = [])
    {
        $this->routes     = $routes;
        $this->configPath = glob(ROOT . '/config/*.php');
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
        foreach ($this->configPath as $configFile) {

            if (! file_exists($configFile)) {
                throw new Exception("Config file not found: $configFile");
            }
            $tempConfigFile            = require_once $configFile;
            $configFile                = str_replace('.php', '', basename($configFile));
            $configFile                = str_replace('-', '_', $configFile);
            $configFile                = str_replace('.', '_', $configFile);
            $configFile                = strtolower($configFile);
            $_ENV[$configFile]         = $tempConfigFile;
        }

    }
    public function sessionInit(){
         Session::init();
    }
    protected function databaseInit()
    {
        Db::connect();
    }
    public function init()
    {
        try {
            $this->sessionInit();
            $this->helpersInit();
            $this->configInit();
            $this->databaseInit();
            $this->routesInit();
        } catch (\Throwable $th) {
            http_response_code($th->getCode());
            $data = [
                'message' => $th->getMessage(),
                'code'    => $th->getCode(),
                'file'    => $th->getFile(),
                'line'    => $th->getLine(),
                'trace'   => $th->getTraceAsString(),
            ];
            if (VIEWS && file_exists(VIEWS . 'errors/error.smile.php')) {
                return view('errors/error', $data);
            }
            return require_once __DIR__ . '/views/errors/error.smile.php';
        }
    }
    public function kernalInit()
    {
        try {
            $this->helpersInit();
            $this->configInit();
            $this->runCommands();
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
        exit;
    }
   public function runCommands()
{
    global $argv;
    $port = 5000;
    $command = null;

    // Loop through arguments to find the port or commands
    foreach ($argv as $arg) {
        if (str_contains($arg, '--port')) {
            // Get port from the argument
            $port = explode('=', $arg)[1];
        } elseif ($arg === 'run') {
            // If 'run' is the command, set the php server command
            $command = 'php -S localhost:' . $port;
        } elseif ($arg === 'smile-me') {
            // If 'smile-me' is the command, initialize and handle smile-me
            if (file_exists(ROOT . '/smile-me.php')) {
                $smileInit = require_once ROOT . '/app/commands/smile-me.php';
                $command = $smileInit->handle();
            } else {
                echo 'Smile init file not found.';
                return;
            }
        }
    }

    // Execute the command if set, otherwise output 'Command not found'
    if ($command) {
        exec($command);
    } else {
        echo 'Command not found';
    }
}

}
