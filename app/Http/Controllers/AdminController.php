<?php

namespace App\Http\Controllers;

use App\Actions\Admin\AdminLogin;
use App\Http\Requests\Admin\AdminLoginRequests;
use Illuminate\Http\JsonResponse;

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
}
