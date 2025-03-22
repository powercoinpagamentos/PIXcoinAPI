<?php

namespace App\Http\Controllers;

use App\Actions\Admin\AdminLogin;
use App\Actions\Customer\AddCustomerWarning;
use App\Actions\Customer\CreateCustomer;
use App\Actions\Customer\DeleteCustomer;
use App\Actions\Customer\GetAllCustomers;
use App\Actions\Customer\GetCustomer;
use App\Actions\Customer\UpdateCustomer;
use App\Actions\Machine\AddRemoteCredit;
use App\Actions\Machine\CreateMachine;
use App\Actions\Machine\DisabledMachine;
use App\Actions\Machine\RemoveMachine;
use App\Actions\Machine\UpdateMachine;
use App\Actions\Payment\GetPayments;
use App\Actions\Payment\GetPaymentsByPeriod;
use App\Actions\Payment\RemovePayments;
use App\Actions\Report\PaymentsCashReport;
use App\Actions\Report\PaymentsRefundsReport;
use App\Actions\Report\PaymentsReport;
use App\Actions\Report\PaymentsTaxReport;
use App\Helpers\AdminHelper;
use App\Http\Requests\Admin\AdminLoginRequests;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Hash;

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

    public function createMachine(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $machineData = [
            'nome' => $request->get('nome'),
            'descricao' => $request->get('descricao'),
            'store_id' => $request->get('store_id'),
            'valorDoPulso' => $request->get('valorDoPulso'),
            'cliente_id' => $request->get('clienteId'),
            'valor_do_pix' => '0',
            'maquininha_serial' => $request->get('maquininha_serial'),
        ];

        return (new CreateMachine($machineData))->run();
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

    public function updateCustomer(Request $request, string $customerId): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $data = [
            'nome' => $request->get('nome'),
            'dataVencimento' => Carbon::parse($request->get('dataVencimento')),
            'pagbankToken' => $request->get('pagbankToken'),
            'pagbankEmail' => $request->get('pagbankEmail'),
            'mercadoPagoToken' => $request->get('mercadoPagoToken'),
        ];
        return (new UpdateCustomer($customerId, $data))->run();
    }

    public function createCustomer(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $data = [
            'nome' => $request->get('nome'),
            'dataVencimento' => Carbon::parse($request->get('dataVencimento')),
            'mercadoPagoToken' => $request->get('mercadoPagoToken'),
            'pagbankToken' => $request->get('pagbankToken'),
            'pagbankEmail' => $request->get('pagbankEmail'),
            'email' => $request->get('email'),
            'senha' => Hash::make($request->get('senha')),
            'pessoa_id' => $userId
        ];

        return (new CreateCustomer($data))->run();
    }

    public function deleteCustomer(Request $request, string $id): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new DeleteCustomer($id))->run();
    }

    public function addCustomerWarning(Request $request, string $id): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new AddCustomerWarning(
            $id,
            $request->get('message'),
            $request->get('showForAll')
        ))->run();
    }
}
