<?php

namespace App\Http\Controllers;

use App\Actions\Customer\CustomerLogin;
use App\Http\Requests\Customer\CustomerLoginRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
}
