<?php

namespace App\Actions\Admin;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class GetAllCustomers
{
    public function run(): JsonResponse
    {
        $customers = $this->getCustomers();
        return response()->json($customers);
    }

    private function getCustomers(): Collection
    {
        return Cliente::whereHas('maquinas')->with('maquinas')->get();

    }
}
