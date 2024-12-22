<?php

namespace App\Actions\Machine;

use App\Models\Maquina;
use Illuminate\Http\JsonResponse;

readonly class ConsultMachine
{
    public function __construct(private string $machineId)
    {
    }

    public function run(): JsonResponse
    {
        $machine = $this->getMachine();
        if (!$machine) {
            return response()->json(['retorno' => '0000']);
        }

        $pulso = '0000';

        if ($machine->valor_do_pix !== '0') {
            $pulso = $this->convertPixValue($machine->valor_do_pix, $machine->valorDoPulso);
        }

        $this->updateMachine();

        return response()->json(['retorno' => $pulso]);
    }

    private function getMachine(): ?Maquina
    {
        return Maquina::query()
            ->where('id', $this->machineId)
            ->where('disabled', false)
            ->select('valorDoPulso', 'valor_do_pix')
            ->first();
    }

    private function updateMachine(): void
    {
        Maquina::query()
            ->where('id', $this->machineId)
            ->update([
            'valor_do_pix' => "0",
            'ultima_requisicao' => now()
        ]);
    }

    private function convertPixValue(float $valorPix, float $valorDoPulso): string
    {
        if ($valorPix <= 0 || $valorDoPulso <= 0 || $valorPix < $valorDoPulso) {
            return "0000";
        }

        $credits = floor($valorPix / $valorDoPulso);
        return str_pad((string) $credits, 4, "0", STR_PAD_LEFT);
    }
}
