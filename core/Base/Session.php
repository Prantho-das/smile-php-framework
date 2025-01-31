<?php
namespace Core\Base;

class Session
{

    public static function init()
    {
        session_start();
    }
    public static function set($key, $value)
    {
        $_SESSION[$key] = (string)$value;
    }
    public static function get($key)
    {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        return null;
    }
    public static function delete($key)
    {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }
    public static function has($key)
    {
        if (isset($_SESSION[$key])) {
            return true;
        }
        return false;
    }
    public static function flush()
    {
        session_unset();
    }
    public static function flash($key, $value)
    {
        $_SESSION[$key] = $value;
    }
}
