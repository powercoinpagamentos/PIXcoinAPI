<?php

namespace App\Actions\Customer;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

readonly class GetAllCustomers
{
    public function run(): JsonResponse
    {
        $customers = $this->getCustomers();
        return response()->json($customers);
    }

    private function getCustomers(): Collection
    {
        return Cliente::with('maquinas')->get();
    }
}
