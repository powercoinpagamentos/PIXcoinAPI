<?php

namespace App\Http\Controllers;

use App\Actions\Customer\CustomerLogin;
use App\Http\Requests\Customer\CustomerLoginRequest;
use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
}
