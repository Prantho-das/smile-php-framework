<?php
namespace App\Controllers;

use Core\Base\BaseController;
use Core\Base\Db;

class HomeController extends BaseController
{
    public function index()
    {
        return view('welcome');
    }
}
