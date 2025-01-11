<?php

namespace App\Actions\Customer;

use App\Models\Cliente;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

readonly class DeleteCustomer
{
    public function __construct(private string $id)
    {
    }

    public function run(): JsonResponse
    {
        $this->removeCustomer();

        return response()->json(['message' => 'Cliente removido com sucesso!']);
    }

    private function removeCustomer(): void
    {
        DB::transaction(function () {
            $cliente = Cliente::find($this->id);
            $cliente->pagamentos()->delete();
            $cliente->delete();
        });
    }
}
