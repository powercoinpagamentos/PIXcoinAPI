<?php

namespace App\Actions\Machine;

use App\Models\Maquina;
use Illuminate\Http\JsonResponse;

readonly class UpdateMachine
{
    public function __construct(private array $machineData, private string $id)
    {
    }

    public function run(): JsonResponse
    {
        $updated = Maquina::query()
            ->where('id', $this->id)
            ->update($this->machineData);

        if ($updated) {
            return response()->json(['message' => 'Máquina atualizada.']);
        }

        return response()->json(['error' => 'Máquina não encontrada'], 404);
    }
}
