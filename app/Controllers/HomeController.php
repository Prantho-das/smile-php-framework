<?php

namespace  App\Controllers;
 use Core\Base\Db; 
use Core\Base\BaseController;


class HomeController extends BaseController
{
    public function index()
    {
        return view('welcome');
    }
    public function news()
    {
        return view('welcome');
    }
    public function details(){
        $query=Db::query("SELECT * FROM users");
         return view('welcome');
    }
}