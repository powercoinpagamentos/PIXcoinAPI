<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\MachineController;
use App\Http\Controllers\PaymentController;
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
Route::delete('/cliente/{id}', [AdminController::class, 'deleteCustomer']);
Route::post('/cliente/{id}/add-warning', [AdminController::class, 'addCustomerWarning']);
Route::post('/cliente/restore-password/{id}', [AdminController::class, 'restorePassword']);

Route::post('/login-cliente', [CustomerController::class, 'login']);
Route::get('/is-client-ok/{clientId}', [CustomerController::class, 'clientOk']);
Route::get('/warning/{clientId}', [CustomerController::class, 'getWarning']);
Route::post('/create-employee', [CustomerController::class, 'createEmployee']);
Route::get('/customer/{id}/employees', [CustomerController::class, 'getEmployees']);
Route::delete('/customer/{id}/employees', [CustomerController::class, 'deleteEmployee']);

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
Route::get('/consultar-maquina/{machineId}', [MachineController::class, 'consultMachine']);
Route::post('/inserir-maquininha', [MachineController::class, 'insertLittleMachine']);
Route::get('/buscar-maquininha/{codigo}', [MachineController::class, 'getLittleMachine']);
Route::put('/alterar-maquininha/{codigo}', [MachineController::class, 'updateLittleMachine']);
Route::delete('/deletar-maquininha/{codigo}', [MachineController::class, 'deleteLittleMachine']);
Route::get('/is-online/{machineId}', [MachineController::class, 'isOnline']);

Route::post('/rota-recebimento-mercado-pago-dinamica/{id}', [PaymentController::class, 'receiptPayment']);
Route::post('/rota-recebimento-especie/{id}', [PaymentController::class, 'receiptPaymentCash']);
Route::post('/webhookmercadopago/{id}', [PaymentController::class, 'testMercadoPago']);
Route::post('/webhookpagbank/{clientId}', [PaymentController::class, 'receiptPaymentFromPagBank']);
Route::post('/mp-qrcode-generator/{clientId}/{machineId}', [PaymentController::class, 'qrCodeGenerator']);

Route::post('/decrementar-estoque/{machineId}', [MachineController::class, 'decrementStock']);
Route::post('/setar-estoque/{machineId}', [MachineController::class, 'incrementStock']);

Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink']);
