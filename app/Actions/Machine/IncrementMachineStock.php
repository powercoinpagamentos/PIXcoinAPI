<?php
declare(strict_types=1);

namespace App\Actions\Machine;

use App\Models\Maquina;
use Illuminate\Http\JsonResponse;

readonly class IncrementMachineStock
{
    public function __construct(
        private string $value,
        private string $machineId
    )
    {
    }

    public function run(): JsonResponse
    {
        $machine = $this->getMachine();

        $this->updateStockMachine($machine->estoque);

        return new JsonResponse(['message' => 'Estoque atualizado'], 200);
    }

    private function getMachine()
    {
        return Maquina::find($this->machineId);
    }

    public function updateStockMachine(int $currentStockValue): void
    {
        $stockToUpdate = $currentStockValue + intval($this->value);
        Maquina::query()
            ->where('id', $this->machineId)
            ->update(['estoque' => $stockToUpdate]);
    }
}
