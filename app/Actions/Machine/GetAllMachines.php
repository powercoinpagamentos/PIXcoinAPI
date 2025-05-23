<?php

namespace App\Actions\Machine;

use App\Helpers\PaymentHelper;
use App\Models\Maquina;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

readonly class GetAllMachines
{
    public function __construct(private string $userId)
    {
    }

    public function run(): JsonResponse
    {
        DB::beginTransaction();

        try {
            $machines = $this->getMachines();

            if ($machines->isEmpty()) {
                DB::rollBack();
                DB::disconnect();
                return response()->json([], 404);
            }

            $machinesWithStatus = $this->machinesWithStatus($machines);

            DB::commit();
            DB::disconnect();

            return response()->json($machinesWithStatus);

        } catch (\Throwable $th) {
            DB::rollBack();
            DB::disconnect();
            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    private function getMachines(): Collection
    {
        return Maquina::where('cliente_id', $this->userId)
            ->orderBy('data_inclusao', 'desc')
            ->get();
    }

    private function machinesWithStatus(Collection $machines): Collection|\Illuminate\Support\Collection
    {
        $paymentHelper = new PaymentHelper();

        return $machines->map(function (Maquina $machine) use ($paymentHelper) {
            $status = 'OFFLINE';
            $now = Carbon::now();

            if ($machine->ultima_requisicao) {
                $lastRequestTime = Carbon::parse($machine->ultima_requisicao);
                $tempoDesdeUltimaRequisicao = $now->diffInSeconds($lastRequestTime);

                $status = $tempoDesdeUltimaRequisicao > 30 ? 'OFFLINE' : 'ONLINE';

                if ($status === 'ONLINE' && $machine->ultimo_pagamento_recebido) {
                    $lastPaymentTime = Carbon::parse($machine->ultimo_pagamento_recebido);
                    $tempoDesdeUltimoPagamento = $now->diffInSeconds($lastPaymentTime);

                    if ($tempoDesdeUltimoPagamento < 30) {
                        $status = 'PAGAMENTO_RECENTE';
                    }
                }
            }

            $pagamentosDaMaquina = $machine->pagamentos()->orderBy('data', 'desc')->get();
            $totais = $paymentHelper->getTotalPayments($pagamentosDaMaquina);

            $pagBankTotais = $totais['pagBankTotais'] ?? [];
            $totalSemEstorno = empty($pagBankTotais)
                ? $totais['totalSemEstorno']
                : $totais['totalSemEstorno'] + $pagBankTotais['totalSemEstorno'];

            $totalComEstorno = empty($pagBankTotais)
                ? $totais['totalComEstorno']
                : $totais['totalComEstorno'] + $pagBankTotais['totalComEstorno'];

            return [
                'id' => $machine->id,
                'pessoa_id' => $machine->pessoa_id,
                'cliente_id' => $machine->cliente_id,
                'nome' => $machine->nome,
                'descricao' => $machine->descricao,
                'estoque' => $machine->estoque,
                'store_id' => $machine->store_id,
                'maquininha_serial' => $machine->maquininha_serial,
                'valor_do_pix' => $machine->valor_do_pix,
                'data_inclusao' => $machine->data_inclusao,
                'ultimo_pagamento_recebido' => $machine->ultimo_pagamento_recebido,
                'ultima_requisicao' => $machine->ultima_requisicao,
                'status' => $status,
                'pulso' => $machine->valorDoPulso,
                'totalSemEstorno' => $totalSemEstorno,
                'totalEspecie' => $totais['totalEspecie'],
                'totalComEstorno' => $totalComEstorno,
                'disabled' => (bool)$machine->disabled,
                'tempoLow' => $machine->tempoLow,
                'tempoHigh' => $machine->tempoHigh,
                'moves' => $machine->moves,
                'bonus' => $machine->bonus,
                'bonusPlay' => (bool)$machine->bonusPlay,
                'tabledBonus' => (bool)$machine->tabledBonus,
                'bonus_five' => $machine->bonus_five,
                'bonus_ten' => $machine->bonus_ten,
                'bonus_twenty' => $machine->bonus_twenty,
                'bonus_fifty' => $machine->bonus_fifty,
                'bonus_hundred' => $machine->bonus_hundred,
            ];
        });
    }
}
