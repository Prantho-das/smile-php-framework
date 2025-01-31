<?php

use App\Controllers\Auth\AuthController;
use App\Controllers\Backend\DashboardController;
use App\Controllers\HomeController;
use App\Middlewares\AuthMiddleware;
use Core\Base\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/register', [AuthController::class,'register']);
Route::get('/login', [AuthController::class,'login']);
Route::post('/authenticate-login', [AuthController::class, 'authenticate']);
Route::get('/admin/dashboard',  [DashboardController::class, 'index'],[
   AuthMiddleware::class
]);
