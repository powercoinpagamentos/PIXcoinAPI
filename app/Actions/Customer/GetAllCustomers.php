<?php

namespace App\Actions\Customer;

use App\Models\Cliente;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

readonly class GetAllCustomers
{
    public function run(): JsonResponse
    {
        DB::beginTransaction();

        try {
            $customers = $this->getCustomers();

            DB::commit();

            return response()->json($customers);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(
                ['error' => 'Falha ao recuperar clientes'],
                500
            );
        } finally {
            DB::disconnect();
        }
    }

    private function getCustomers(): Collection
    {
        return Cliente::with('maquinas')->get();
    }
}
