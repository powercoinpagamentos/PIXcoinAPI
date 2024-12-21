<?php

namespace App\Http\Controllers;

use App\Actions\Machine\GetAllMachines;
use App\Actions\Machine\GetPayments;
use App\Actions\Machine\RemovePayments;
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

    public function removePayments(Request $request, string $machineId): JsonResponse
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return (new RemovePayments($machineId))->run();
    }
}
