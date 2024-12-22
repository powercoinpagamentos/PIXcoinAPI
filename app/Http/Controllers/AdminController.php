<?php

namespace App\Http\Controllers;

use App\Actions\Admin\AdminLogin;
use App\Actions\Admin\GetAllCustomers;
use App\Actions\Admin\GetCustomer;
use App\Actions\Machine\AddRemoteCredit;
use App\Actions\Machine\DisabledMachine;
use App\Actions\Machine\GetPayments;
use App\Actions\Machine\GetPaymentsByPeriod;
use App\Actions\Machine\RemoveMachine;
use App\Actions\Machine\RemovePayments;
use App\Actions\Machine\UpdateMachine;
use App\Actions\Report\PaymentsCashReport;
use App\Actions\Report\PaymentsRefundsReport;
use App\Actions\Report\PaymentsReport;
use App\Actions\Report\PaymentsTaxReport;
use App\Helpers\AdminHelper;
use App\Http\Requests\Admin\AdminLoginRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class AdminController extends Controller
{
    /**
     * @param AdminLoginRequests $request
     * @return JsonResponse
     */
    public function login(AdminLoginRequests $request): JsonResponse
    {
        $adminLoginAction = new AdminLogin($request->email, $request->senha);
        return $adminLoginAction->run();
    }

    public function paymentsByPeriod(Request $request, string $machineId): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $startDate = $request->get('dataInicio');
        $endDate = $request->get('dataFim');

        return (new GetPaymentsByPeriod($machineId, $startDate, $endDate))->run();
    }

    public function paymentsFromMachine(Request $request, string $machineId): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new GetPayments($machineId))->run();
    }

    public function removePayments(Request $request, string $machineId): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new RemovePayments($machineId))->run();
    }

    public function paymentsCashReport(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
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
        $userId = (new AdminHelper())->validateAdminToken($token);
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
        $userId = (new AdminHelper())->validateAdminToken($token);
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
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $machineId = $request->get('maquinaId');
        $startDate = $request->get('dataInicio');
        $endDate = $request->get('dataFim');

        return (new PaymentsRefundsReport($machineId, $startDate, $endDate))->run();
    }

    public function updateMachine(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $machineData = $request->all();
        Arr::forget($machineData, 'id');

        return (new UpdateMachine($machineData, $request->get('id')))->run();
    }

    public function deleteMachine(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new RemoveMachine($request->get('id')))->run();
    }

    public function addRemoteCreditOnMachine(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new AddRemoteCredit($request->get('id'), $request->get('valor')))->run();
    }

    public function disabledMachine(Request $request, string $customerId): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new DisabledMachine($customerId))->run();
    }

    public function allCustomers(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new GetAllCustomers())->run();
    }

    public function getCustomer(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $customerId = $request->get('id');

        return (new GetCustomer($customerId))->run();
    }
}
