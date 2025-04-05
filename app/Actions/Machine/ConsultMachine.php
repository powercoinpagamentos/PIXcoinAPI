<?php

namespace App\Actions\Machine;

use App\Models\Maquina;
use App\Models\Pagamento;
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
        $currentPixValue = $machine->valor_do_pix;

        $this->handleBonusPlay($machine);

        if ($machine->valor_do_pix !== '0') {
            $pulso = $this->convertPixValue($machine->valor_do_pix, $machine->valorDoPulso);
        }

        if (
            $machine->bonusPlay &&
            $pulso !== '0000'
            && $currentPixValue === $machine->valor_do_pix
        ) {
            $machine->increment('moves_count');
        }

        $this->updateMachine($machine);

        return response()->json([
            'retorno' => $pulso,
            'tempoLow' => $machine->tempoLow,
            'tempoHigh' => $machine->tempoHigh,
        ]);
    }

    private function getMachine(): ?Maquina
    {
        return Maquina::query()
            ->where('id', $this->machineId)
            ->where('disabled', false)
            ->select(
                'valorDoPulso',
                'valor_do_pix',
                'tempoHigh',
                'tempoLow',
                'moves_count',
                'bonusPlay',
                'moves',
                'bonus',
                'cliente_id',
                'id'
            )
            ->first();
    }

    private function updateMachine(Maquina $machine): void
    {
        $machine->valor_do_pix = "0";
        $machine->ultima_requisicao = now();
        $machine->save();
    }

    private function convertPixValue(float|string $valorPix, float $valorDoPulso): string
    {
        if ((float)$valorPix <= 0 || $valorDoPulso <= 0 || (float)$valorPix < $valorDoPulso) {
            return "0000";
        }

        $credits = floor((float)$valorPix / $valorDoPulso);
        return str_pad((string)$credits, 4, "0", STR_PAD_LEFT);
    }

    private function handleBonusPlay(Maquina $machine): void
    {
        if ($machine->bonusPlay && $machine->moves_count >= $machine->moves ) {
            $machine->moves_count = 0;
            $newPixValue = $machine->bonus + $machine->valor_do_pix;
            $machine->valor_do_pix = "$newPixValue";

            $this->createPaymentOnTelemetryWhenBonusPlay($machine);
        }
    }

    private function createPaymentOnTelemetryWhenBonusPlay(Maquina $machine): void
    {
        Pagamento::create([
            'maquina_id' => $machine->id,
            'valor' => $machine->bonus,
            'mercadoPagoId' => 'JOGADA BÔNUS',
            'tipo' => 'bonus',
            'data' => now(),
            'estornado' => false,
            'cliente_id' => $machine->cliente_id
        ]);
    }
}
