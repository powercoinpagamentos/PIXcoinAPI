<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MachineController;
use Illuminate\Support\Facades\Route;

Route::post('/login-pessoa', [AdminController::class, 'login']);
Route::delete('/delete-pagamentos-adm/{machineId}', [AdminController::class, 'removePayments']);

Route::post('/login-cliente', [CustomerController::class, 'login']);

Route::get('/maquinas', [MachineController::class, 'all']);

Route::get('/pagamentos/{machineId}', [MachineController::class, 'payments']);
Route::post('/pagamentos-periodo/{machineId}', [MachineController::class, 'paymentsByPeriod']);

Route::delete('/delete-pagamentos/{machineId}', [MachineController::class, 'removePayments']);
Route::post('/delete-selected-payments', [MachineController::class, 'removeSelectedPayments']);
