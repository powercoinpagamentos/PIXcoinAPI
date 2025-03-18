<?php
declare(strict_types=1);

namespace App\Actions\Machine;

use App\Models\Maquina;
use Illuminate\Http\JsonResponse;

readonly class DecrementMachineStock
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

        if ($machine->estoque === 0) {
            return new JsonResponse(['message' => 'Estoque vazio'], 200);
        }

        $this->updateStockMachine($machine->estoque);

        return new JsonResponse(['message' => 'Estoque atualizado'], 200);
    }

    private function getMachine()
    {
        return Maquina::find($this->machineId);
    }

    public function updateStockMachine(int $currentStockValue)
    {
        $stockToUpdate = $currentStockValue - $this->value;
        Maquina::query()
            ->where('id', $this->machineId)
            ->update(['estoque' => max($stockToUpdate, 0)]);
    }
}
