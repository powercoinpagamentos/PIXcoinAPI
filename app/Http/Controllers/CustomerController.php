<?php

namespace App\Http\Controllers;

use App\Actions\Customer\CustomerLogin;
use App\Http\Requests\Customer\CustomerLoginRequest;
use Illuminate\Http\JsonResponse;

class CustomerController extends Controller
{
    public function login(CustomerLoginRequest $request): JsonResponse
    {
        $adminLoginAction = new CustomerLogin($request->email, $request->senha);
        return $adminLoginAction->run();
    }
}
