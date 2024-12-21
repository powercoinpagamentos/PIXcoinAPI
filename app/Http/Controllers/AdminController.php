<?php

namespace App\Http\Controllers;

use App\Actions\Admin\AdminLogin;
use App\Actions\Machine\RemovePayments;
use App\Helpers\AdminHelper;
use App\Http\Requests\Admin\AdminLoginRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

    public function removePayments(Request $request, string $machineId): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new AdminHelper())->validateAdminToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new RemovePayments($machineId))->run();
    }
}
