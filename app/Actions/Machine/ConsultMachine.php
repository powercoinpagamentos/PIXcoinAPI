<?php

namespace App\Actions\Machine;

use App\Models\Maquina;
use App\Models\Pagamento;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

readonly class ConsultMachine
{
    public function __construct(private string $machineId)
    {
    }

    public function run(): JsonResponse
    {
        try {
            $machine = $this->getMachine();
            if (!$machine) {
                Log::error("[ConsultMachine]: Máquina não encontrada.");
                return response()->json(['retorno' => '0000']);
            }

            $currentPixValue = $machine->valor_do_pix;
            if ($currentPixValue === "0") {
                $machine->ultima_requisicao = now();
                $machine->save();

                return response()->json([
                    'retorno' => "0000",
                    'tempoLow' => $machine->tempoLow,
                    'tempoHigh' => $machine->tempoHigh,
                ]);
            }

            if ($machine->is_remote_credit) {
                $pulso = $this->convertPixValue($machine->valor_do_pix, $machine->valorDoPulso);

                $machine->valor_do_pix = "0";
                $machine->ultima_requisicao = now();
                $machine->is_remote_credit = false;
                $machine->save();

                Log::info("[ConsultMachine]: Jogada de crédito remoto na máquina: $machine->id do cliente $machine->cliente_id");
                return response()->json([
                    'retorno' => $pulso,
                    'tempoLow' => $machine->tempoLow,
                    'tempoHigh' => $machine->tempoHigh,
                ]);
            }

            $pulso = '0000';

            $this->handleBonusPlay($machine);
            $machine = $this->handleTabledBonus($machine);

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

            Log::info("ENTROU PIX AEEEE");
            return response()->json([
                'retorno' => $pulso,
                'tempoLow' => $machine->tempoLow,
                'tempoHigh' => $machine->tempoHigh,
            ]);
        } catch (\Exception $e) {
            Log::error("[ConsultMachine]: Erro: " . $e->getMessage());
            return response()->json(['retorno' => '0000']);
        }
    }

    private function getMachine(): ?Maquina
    {
        try {
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
                    'id',
                    'tabledBonus',
                    'bonus_five',
                    'bonus_ten',
                    'bonus_twenty',
                    'bonus_fifty',
                    'bonus_hundred',
                    'is_remote_credit'
                )
                ->first();
        } catch (QueryException $e) {
            Log::error("Falha ao obter máquina para devolver crédito: " . $e->getMessage());
            return null;
        }
    }

    private function updateMachine(Maquina $machine): void
    {
        $machine->valor_do_pix = "0";
        $machine->ultima_requisicao = now();
        $machine->save();
    }

    private function convertPixValue(float|string $pixValue, float $pulseValue): string
    {
        $numericPixValue = (float) $pixValue;

        if ($numericPixValue <= 0 || $pulseValue <= 0) {
            return '0000';
        }

        if ($numericPixValue < $pulseValue) {
            return '0000';
        }

        $credits = (int) floor($numericPixValue / $pulseValue);

        return str_pad((string) $credits, 4, '0', STR_PAD_LEFT);
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

    private function handleTabledBonus(Maquina $machine): Maquina
    {
        if (!$machine->tabledBonus) {
            return $machine;
        }

        $valorPix = (float) $machine->valor_do_pix;

        if ($valorPix <= 0) {
            return $machine;
        }

        $bonusRules = [
            100 => $machine->bonus_hundred ?? 0,
            50 => $machine->bonus_fifty ?? 0,
            20 => $machine->bonus_twenty ?? 0,
            10 => $machine->bonus_ten ?? 0,
            5 => $machine->bonus_five ?? 0
        ];

        $totalBonus = 0;

        foreach ($bonusRules as $multiple => $bonus) {
            if ($valorPix % $multiple === 0) {
                $timesToApply = (int) ($valorPix / $multiple);
                $totalBonus = $bonus * $timesToApply;
                break;
            }
        }

        if ($totalBonus > 0) {
            $machine->valor_do_pix = (string) ($valorPix + $totalBonus);
        }

        return $machine;
    }
}
