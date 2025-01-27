<?php

if (! function_exists('response')) {
    function response($data, $status = 200, $headers = [])
    {
        http_response_code($status);
        header('Content-type: application/json');
        foreach ($headers as $key => $value) {
            header($key . ': ' . $value);
        }
        echo json_encode($data);
    }
}

if (! function_exists('dd')) {
    function dd()
    {
        $args = func_get_args();
        foreach ($args as $arg) {
            echo '<pre>';
            var_dump($arg);
            echo '</pre>';
            echo "<hr>";
        }
        die();
    }
}

if (! function_exists('view')) {
    function view($view, $data = [])
    {
        require_once ROOT . '/views/' . $view . '.smile.php';
    }
}

if (! function_exists('request')) {
    function request($key = null, $default = null)
    {
        $data      = array_merge($_GET, $_POST);
        $jsonInput = file_get_contents('php://input');
        if (! empty($jsonInput)) {
            $jsonData = json_decode($jsonInput, true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $data = array_merge($data, $jsonData);
            }
        }

        if ($key) {
             return $data[$key] ?? $default;
        }

        return $data;

    }
}
