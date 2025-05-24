<?php
declare(strict_types=1);

namespace App\Actions\Machine;

use App\Models\Maquina;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

readonly class DecrementMachineStock
{
    public function __construct(
        private string $value,
        private string $machineId
    ) {}

    public function run(): JsonResponse
    {
        try {
            DB::beginTransaction();

            $machine = $this->getMachine();

            if (!$machine) {
                DB::rollBack();
                return new JsonResponse(['message' => 'Máquina não encontrada'], 404);
            }

            if (empty($machine->estoque)) {
                DB::commit();
                return new JsonResponse(['message' => 'Estoque vazio'], 200);
            }

            $this->updateStockMachine($machine->estoque);

            DB::commit();

            return new JsonResponse([
                'message' => 'Estoque atualizado',
                'new_stock' => max($machine->estoque - $this->value, 0)
            ], 200);

        } catch (Throwable $e) {
            DB::rollBack();
            return new JsonResponse([
                'message' => 'Falha ao atualizar estoque',
                'error' => $e->getMessage()
            ], 500);
        } finally {
            DB::disconnect();
        }
    }

    private function getMachine(): ?Maquina
    {
        return Maquina::query()
            ->select(['id', 'estoque'])
            ->where('id', $this->machineId)
            ->first();
    }

    private function updateStockMachine(int $currentStockValue): void
    {
        $stockToUpdate = $currentStockValue - $this->value;

        Maquina::query()
            ->where('id', $this->machineId)
            ->update(['estoque' => max($stockToUpdate, 0)]);
    }
}
