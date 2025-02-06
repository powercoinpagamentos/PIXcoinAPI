<?php

namespace App\Http\Controllers;

use App\Actions\Machine\AddRemoteCredit;
use App\Actions\Machine\ConsultMachine;
use App\Actions\Machine\CreateLittleMachine;
use App\Actions\Machine\GetAllMachines;
use App\Actions\Machine\GetLittleMachine;
use App\Actions\Machine\RemoveSelectedPayments;
use App\Actions\Machine\UpdateLittleMachine;
use App\Actions\Machine\UpdateMachine;
use App\Actions\Payment\GetPayments;
use App\Actions\Payment\GetPaymentsByPeriod;
use App\Actions\Payment\RemovePayments;
use App\Actions\Report\PaymentsCashReport;
use App\Actions\Report\PaymentsPDFReport;
use App\Actions\Report\PaymentsRefundsReport;
use App\Actions\Report\PaymentsReport;
use App\Actions\Report\PaymentsTaxReport;
use App\Helpers\CustomerHelper;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class MachineController extends Controller
{
    public function all(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new GetAllMachines($userId))->run();
    }

    public function update(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $machineData = $request->all();
        Arr::forget($machineData, 'id');

        return (new UpdateMachine($machineData, $request->get('id')))->run();
    }

    public function addRemoteCredit(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new AddRemoteCredit($request->get('id'), $request->get('valor')))->run();
    }

    public function payments(Request $request, string $machineId): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new GetPayments($machineId))->run();
    }

    public function paymentsByPeriod(Request $request, string $machineId): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $startDate = $request->get('dataInicio');
        $endDate = $request->get('dataFim');

        return (new GetPaymentsByPeriod($machineId, $startDate, $endDate))->run();
    }

    public function removePayments(Request $request, string $machineId): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new RemovePayments($machineId))->run();
    }

    public function removeSelectedPayments(Request $request): JsonResponse
    {
        $machineId = $request->get('machineId');
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');

        return (new RemoveSelectedPayments($machineId, $startDate, $endDate))->run();
    }

    public function paymentsCashReport(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $machineId = $request->get('maquinaId');
        $startDate = $request->get('dataInicio');
        $endDate = $request->get('dataFim');

        return (new PaymentsCashReport($machineId, $startDate, $endDate))->run();
    }

    public function paymentsTaxReport(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $machineId = $request->get('maquinaId');
        $startDate = $request->get('dataInicio');
        $endDate = $request->get('dataFim');

        return (new PaymentsTaxReport($machineId, $startDate, $endDate))->run();
    }

    public function paymentsReport(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $machineId = $request->get('maquinaId');
        $startDate = $request->get('dataInicio');
        $endDate = $request->get('dataFim');

        return (new PaymentsReport($machineId, $startDate, $endDate))->run();
    }

    public function paymentsRefundsReport(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $machineId = $request->get('maquinaId');
        $startDate = $request->get('dataInicio');
        $endDate = $request->get('dataFim');

        return (new PaymentsRefundsReport($machineId, $startDate, $endDate))->run();
    }

    public function paymentsReportPDF(Request $request): Response
    {
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $machineId = $request->get('machineId');

        $data = (new PaymentsPDFReport($machineId, $startDate, $endDate))->run();
        $pdf = PDF::loadView('pdf.payment_report', $data);

        return $pdf->download('relatorio_pagamentos.pdf');
    }

    public function consultMachine(Request $request, string $machineId): JsonResponse
    {
        return (new ConsultMachine($machineId))->run();
    }

    public function insertLittleMachine(Request $request): JsonResponse
    {
        return (new CreateLittleMachine($request->all()))->run();
    }

    public function getLittleMachine(Request $request, string $code): JsonResponse
    {
        return (new GetLittleMachine($code))->run();
    }

    public function updateLittleMachine(Request $request, string $code): JsonResponse
    {
        return (new UpdateLittleMachine($request->all(), $code))->run();
    }
}
