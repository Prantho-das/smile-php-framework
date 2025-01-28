<?php

use App\Controllers\HomeController;
use Core\Base\Route;

Route::get('/', [HomeController::class, 'index']);
Route::get('/news', [HomeController::class, 'news']);
Route::get('/news/:id', [HomeController::class, 'details']);