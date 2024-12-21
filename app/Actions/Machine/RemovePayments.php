<?php

namespace App\Actions\Machine;

use App\Models\Pagamento;
use Illuminate\Http\JsonResponse;

readonly class RemovePayments
{
    public function __construct(private string $machineId)
    {
    }

    public function run(): JsonResponse
    {
        $paymentsRemoved = Pagamento::where('maquina_id', $this->machineId)->delete();
        return response()->json(['message' => "Foram removido(s) $paymentsRemoved pagamento(s) dá máquina $this->machineId."]);
    }
}
