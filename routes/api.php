<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use Illuminate\Support\Facades\Route;

Route::post('/login-pessoa', [AdminController::class, 'login']);

Route::post('/login-cliente', [CustomerController::class, 'login']);
