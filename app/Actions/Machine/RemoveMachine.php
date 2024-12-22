<?php

namespace App\Actions\Machine;

use App\Models\Maquina;
use Illuminate\Http\JsonResponse;

class RemoveMachine
{
    public function __construct(private string $machineId)
    {
    }

    public function run(): JsonResponse
    {
        $maquina = $this->getMachine();
        if ($maquina) {
            $maquina->pagamentos()->delete();
            $maquina->delete();

            return response()->json(['message' => 'Máquina e pagamentos associados deletados com sucesso.']);
        }
        return response()->json(['message' => 'Máquina não encontrada.'], 404);
    }

    private function getMachine(): ?Maquina
    {
        return Maquina::find($this->machineId);
    }
}
