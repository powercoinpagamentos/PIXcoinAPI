<?php

namespace App\Actions\Machine;

use App\Models\Maquina;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

readonly class AddRemoteCredit
{
    public function __construct(private string $id, private string $value)
    {
    }

    public function run(): JsonResponse
    {
        $machine = $this->getMachine();

        if ($this->machineOffline($machine)) {
            return response()->json(['msg' => 'MÁQUINA OFFLINE!'], 400);
        }

        $machine->update([
            'ultimo_pagamento_recebido' => now(),
            'valor_do_pix' => $this->value
        ]);

        return response()->json(['retorno' => 'CREDITO INSERIDO']);
    }

    private function getMachine()
    {
        return Maquina::query()->find($this->id);
    }

    private function machineOffline(Maquina $machine): bool
    {
        if ($machine->ultima_requisicao) {
            $tempoDesdeUltimaRequisicao = abs($this->tempoOffline(Carbon::parse($machine->ultima_requisicao)));
            $tempoDesdeUltimoPagamento = $machine->ultimo_pagamento_recebido
                ? abs($this->tempoOffline(Carbon::parse($machine->ultimo_pagamento_recebido)))
                : PHP_INT_MAX;

            $status = $tempoDesdeUltimaRequisicao > 60 ? 'OFFLINE' : 'ONLINE';

            if ($status === 'ONLINE' && $tempoDesdeUltimoPagamento < 1800) {
                $status = 'PAGAMENTO_RECENTE';
            }

            return $status === 'OFFLINE';
        }

        return true;
    }

    private function tempoOffline(Carbon $data): int
    {
        return Carbon::now()->diffInSeconds($data);
    }
}
