<?php

namespace App\Http\Controllers;

use App\Actions\Machine\GetAllMachines;
use App\Helpers\CustomerHelper;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    public function all(Request $request)
    {
        $token = $request->header('x-access-token');
        $userId = (new CustomerHelper())->validateToken($token);
        if (!$userId) {
            return response()->json(['error' => 'Usuário sem autorização.'], 401);
        }

        return ((new GetAllMachines($userId))->run());
    }
}
