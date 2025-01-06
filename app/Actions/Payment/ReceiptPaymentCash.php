<?php

namespace App\Actions\Payment;

use App\Models\Maquina;
use App\Models\Pagamento;
use Illuminate\Http\JsonResponse;

readonly class ReceiptPaymentCash
{
    public function __construct(private string $machineId, private string $value)
    {
    }

    public function run(): JsonResponse
    {
        $machine = $this->getMachine();
        if (!$machine) {
            return response()->json(['error' => 'Máquina não encontrada'], 404);
        }

        if (!$machine->store_id) {
            return response()->json(['error' => 'Máquina não possui store id'], 500);
        }

        $this->createPayment();

        return response()->json([
            'message' => 'Pagamento registrado com sucesso',
            'tempoLow' => $machine->tempoLow,
            'tempoHigh' => $machine->tempoHigh,
        ]);
    }

    private function getMachine(): ?Maquina
    {
        return Maquina::find($this->machineId);
    }

    private function createPayment(): void
    {
        Pagamento::create([
            'maquina_id' => $this->machineId,
            'valor' => $this->value,
            'mercadoPagoId' => 'CASH',
            'tipo' => 'CASH',
            'data' => now()
        ]);
    }
}
