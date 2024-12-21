<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MachineController;
use Illuminate\Support\Facades\Route;

Route::post('/login-pessoa', [AdminController::class, 'login']);
Route::post('/pagamentos-periodo-adm/{machineId}', [AdminController::class, 'paymentsByPeriod']);
Route::delete('/delete-pagamentos-adm/{machineId}', [AdminController::class, 'removePayments']);
Route::post('/relatorio-03-pagamentos', [AdminController::class, 'paymentsReport']);
Route::post('/relatorio-04-estornos', [AdminController::class, 'paymentsRefundsReport']);

Route::post('/login-cliente', [CustomerController::class, 'login']);

Route::get('/maquinas', [MachineController::class, 'all']);
Route::get('/pagamentos/{machineId}', [MachineController::class, 'payments']);
Route::post('/pagamentos-periodo/{machineId}', [MachineController::class, 'paymentsByPeriod']);
Route::delete('/delete-pagamentos/{machineId}', [MachineController::class, 'removePayments']);
Route::post('/delete-selected-payments', [MachineController::class, 'removeSelectedPayments']);
Route::post('/relatorio-03-pagamentos', [MachineController::class, 'paymentsReport']);
Route::post('/relatorio-04-estornos', [MachineController::class, 'paymentsRefundsReport']);
