<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MachineController;
use Illuminate\Support\Facades\Route;

Route::post('/login-pessoa', [AdminController::class, 'login']);
Route::post('/pagamentos-periodo-adm/{machineId}', [AdminController::class, 'paymentsByPeriod']);
Route::delete('/delete-pagamentos-adm/{machineId}', [AdminController::class, 'removePayments']);
Route::get('/pagamentos-adm/{machineId}', [AdminController::class, 'paymentsFromMachine']);
Route::post('/relatorio-01-cash-adm', [AdminController::class, 'paymentsCashReport']);
Route::post('/relatorio-02-taxas-adm', [AdminController::class, 'paymentsTaxReport']);
Route::post('/relatorio-03-pagamentos-adm', [AdminController::class, 'paymentsReport']);
Route::post('/relatorio-04-estornos-adm', [AdminController::class, 'paymentsRefundsReport']);
Route::put('/maquina', [AdminController::class, 'updateMachine']);
Route::delete('/maquina', [AdminController::class, 'deleteMachine']);
Route::post('/maquina', [AdminController::class, 'createMachine']);
Route::post('/disabled-machine-by-customer/{customerId}', [AdminController::class, 'disabledMachine']);
Route::post('/credito-remoto', [AdminController::class, 'addRemoteCreditOnMachine']);
Route::get('/clientes', [AdminController::class, 'allCustomers']);
Route::get('/cliente', [AdminController::class, 'getCustomer']);
Route::put('/alterar-cliente-adm-new/{id}', [AdminController::class, 'updateCustomer']);
Route::post('/cliente', [AdminController::class, 'createCustomer']);

Route::post('/login-cliente', [CustomerController::class, 'login']);

Route::get('/maquinas', [MachineController::class, 'all']);
Route::put('/maquina-cliente', [MachineController::class, 'update']);
Route::post('/credito-remoto-cliente', [MachineController::class, 'addRemoteCredit']);
Route::get('/pagamentos/{machineId}', [MachineController::class, 'payments']);
Route::post('/pagamentos-periodo/{machineId}', [MachineController::class, 'paymentsByPeriod']);
Route::delete('/delete-pagamentos/{machineId}', [MachineController::class, 'removePayments']);
Route::post('/delete-selected-payments', [MachineController::class, 'removeSelectedPayments']);
Route::post('/relatorio-01-cash', [MachineController::class, 'paymentsCashReport']);
Route::post('/relatorio-02-taxas', [MachineController::class, 'paymentsTaxReport']);
Route::post('/relatorio-03-pagamentos', [MachineController::class, 'paymentsReport']);
Route::post('/relatorio-04-estornos', [MachineController::class, 'paymentsRefundsReport']);
Route::post('/relatorio-pagamento-pdf', [MachineController::class, 'paymentsReportPDF']);
