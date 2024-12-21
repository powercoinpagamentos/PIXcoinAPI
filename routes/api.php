<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MachineController;
use Illuminate\Support\Facades\Route;

Route::post('/login-pessoa', [AdminController::class, 'login']);

Route::post('/login-cliente', [CustomerController::class, 'login']);

Route::get('/maquinas', [MachineController::class, 'all']);

Route::get('/pagamentos/{machineId}', [MachineController::class, 'payments']);

Route::delete('/delete-pagamentos/{machineId}', [MachineController::class, 'removePayments']);
