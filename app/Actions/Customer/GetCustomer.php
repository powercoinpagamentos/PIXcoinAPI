<?php

namespace App\Actions\Customer;

use App\Helpers\PaymentHelper;
use App\Models\Cliente;
use App\Models\Maquina;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

readonly class GetCustomer
{
    public function __construct(private string $id)
    {
    }

    public function run(): JsonResponse
    {
        $customer = $this->getCustomer();
        $machinesWithStatus = $this->machinesWithStatus($customer->maquinas);

        $customer->setRelation('maquinas', $machinesWithStatus);

        return response()->json($customer);
    }

    private function getCustomer(): Cliente
    {
        return Cliente::with('maquinas')->find($this->id);
    }

    private function machinesWithStatus(Collection $machines): Collection|\Illuminate\Support\Collection
    {
        return $machines->map(function (Maquina $machine) {
            $status = 'OFFLINE';

            if ($machine->ultima_requisicao) {
                $tempoDesdeUltimaRequisicao = abs($this->tempoOffline(Carbon::parse($machine->ultima_requisicao)));
                $tempoDesdeUltimoPagamento = $machine->ultimo_pagamento_recebido
                    ? abs($this->tempoOffline(Carbon::parse($machine->ultimo_pagamento_recebido)))
                    : PHP_INT_MAX;

                $status = $tempoDesdeUltimaRequisicao > 20 ? 'OFFLINE' : 'ONLINE';

                if ($status === 'ONLINE' && $tempoDesdeUltimoPagamento < 60) {
                    $status = 'PAGAMENTO_RECENTE';
                }
            }

            $pagamentosDaMaquina = $machine->pagamentos()
                ->orderBy('data', 'desc')
                ->get();

            $totais = (new PaymentHelper())->getTotalPayments($pagamentosDaMaquina);
            $pagBankTotais = $totais['pagBankTotais'];
            $totalSemEstorno = empty($pagBankTotais) ? $totais['totalSemEstorno'] : $totais['totalSemEstorno'] + $pagBankTotais['totalSemEstorno'];
            $totalComEstorno = empty($pagBankTotais) ? $totais['totalComEstorno'] : $totais['totalComEstorno'] + $pagBankTotais['totalComEstorno'];

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
            ];
        });
    }

    public function tempoOffline(Carbon $data): int
    {
        return Carbon::now()->diffInSeconds($data);
    }
}
