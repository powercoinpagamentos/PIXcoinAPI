<?php

namespace App\Actions\Machine;

use App\Helpers\PaymentHelper;
use App\Models\Maquina;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

readonly class GetAllMachines
{
    public function __construct(private string $userId)
    {
    }

    public function run(): JsonResponse
    {
        $machines = $this->getMachines();
        if ($machines->isEmpty()) {
            return response()->json([], 404);
        }

        try {
            $machinesWithStatus = $this->machinesWithStatus($machines);

            return response()->json($machinesWithStatus);
        } catch (\Throwable $th) {
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
        return $machines->map(function (Maquina $machine) {
            $status = 'OFFLINE';

            if ($machine->ultima_requisicao) {
                $tempoDesdeUltimaRequisicao = abs($this->tempoOffline(Carbon::parse($machine->ultima_requisicao)));
                $tempoDesdeUltimoPagamento = $machine->ultimo_pagamento_recebido
                    ? abs($this->tempoOffline(Carbon::parse($machine->ultimo_pagamento_recebido)))
                    : PHP_INT_MAX;

                $status = $tempoDesdeUltimaRequisicao > 60 ? 'OFFLINE' : 'ONLINE';

                if ($status === 'ONLINE' && $tempoDesdeUltimoPagamento < 1800) {
                    $status = 'PAGAMENTO_RECENTE';
                }
            }

            $pagamentosDaMaquina = $machine->pagamentos()
                ->orderBy('data', 'desc')
                ->get();

            $totais = (new PaymentHelper())->getTotalPayments($pagamentosDaMaquina);

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
                'totalSemEstorno' => $totais['totalSemEstorno'],
                'totalEspecie' => $totais['totalEspecie'],
                'totalComEstorno' => $totais['totalComEstorno'],
                'disabled' => (bool)$machine->disabled,
                'tempoDoPulso' => $machine->tempoDoPulso
            ];
        });
    }

    public function tempoOffline(Carbon $data): int
    {
        return Carbon::now()->diffInSeconds($data);
    }
}
