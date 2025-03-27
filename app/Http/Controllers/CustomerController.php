<?php

namespace App\Http\Controllers;

use App\Actions\Customer\AddEmployee;
use App\Actions\Customer\CustomerLogin;
use App\Helpers\CustomerHelper;
use App\Http\Requests\Customer\CustomerLoginRequest;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class CustomerController extends Controller
{
    public function login(CustomerLoginRequest $request): JsonResponse
    {
        $adminLoginAction = new CustomerLogin($request->email, $request->senha);
        return $adminLoginAction->run();
    }

    public function clientOk(Request $request, string $clientId): JsonResponse
    {
        // @TODO: Validar cliente inadimplente
        return new JsonResponse(['status' => true]);
    }

    public function getWarning(Request $request, string $clientId): JsonResponse
    {
        $client = Cliente::find($clientId) ?? throw new NotFoundHttpException('Customer not found!');

        return response()->json(['message' => $client->aviso ?? null]);
    }

    public function createEmployee(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new AddEmployee())->run([
            'nome' => $request->get('nome'),
            'email' => $request->get('email'),
            'senha' => Hash::make($request->get('senha')),
            'parent_id' => $request->get('id'),
            'can_delete_payments' => $request->get('canDelete') === 'null' ? 0 : 1,
            'can_add_remote_credit' => $request->get('canAddCredit') === 'null' ? 0 : 1,
            'can_add_edit_machine' => $request->get('canEditMachine') === 'null' ? 0 : 1,
            'is_employee' => 1,
        ]);
    }

    public function getEmployees(Request $request): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        $customer = Cliente::find($userId);

        return new JsonResponse($customer->employees ?? []);
    }

    public function deleteEmployee(Request $request, string $employeeId): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        Cliente::destroy($employeeId);

        return response()->json(['message' => 'Funcionário removido']);
    }
}
