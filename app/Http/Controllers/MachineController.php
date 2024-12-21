<?php

namespace App\Http\Controllers;

use App\Actions\Machine\GetAllMachines;
use App\Actions\Machine\GetPayments;
use App\Actions\Machine\GetPaymentsByPeriod;
use App\Actions\Machine\RemovePayments;
use App\Actions\Machine\RemoveSelectedPayments;
use App\Actions\Report\PaymentsCashReport;
use App\Actions\Report\PaymentsRefundsReport;
use App\Actions\Report\PaymentsReport;
use App\Actions\Report\PaymentsTaxReport;
use App\Helpers\CustomerHelper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
