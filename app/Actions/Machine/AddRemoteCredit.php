<?php

namespace App\Actions\Machine;

use App\Models\Maquina;
use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class AddRemoteCredit
{
    public function __construct(private string $id, private string $value)
    {
    }

    public function run(): JsonResponse
    {
        DB::beginTransaction();
        $machine = $this->getMachine();

        if (!$machine) {
            DB::rollBack();
            DB::disconnect();
            return response()->json(['msg' => 'Falha na operação - Entre em contato com o suporte!'], 400);
        }

        if ($this->machineOffline($machine)) {
            DB::rollBack();
            DB::disconnect();
            return response()->json(['msg' => 'MÁQUINA OFFLINE!'], 400);
        }

        if ((float) $this->value < $machine->valorDoPulso) {
            DB::rollBack();
            DB::disconnect();
            return response()->json(['msg' => "Valor do pulso abaixo do configurado. Valor configurado: $machine->valorDoPulso"], 400);
        }

        $machine->update([
            'ultimo_pagamento_recebido' => now(),
            'valor_do_pix' => $this->value,
            'is_remote_credit' => true,
        ]);

        $this->createPayment($machine->id, $this->value, $machine->cliente_id);

        DB::commit();
        DB::disconnect();

        return response()->json(['retorno' => 'CREDITO INSERIDO']);
    }

    private function getMachine(): ?Maquina
    {
        try {
            return Maquina::query()->find($this->id);
        } catch (QueryException $exception) {
            Log::error("Falha ao obter máquina para crédito remoto: " . $exception->getMessage());
            return null;
        }
    }

    private function machineOffline(Maquina $machine): bool
    {
        if ($machine->ultima_requisicao) {
            $tempoDesdeUltimaRequisicao = abs($this->tempoOffline(Carbon::parse($machine->ultima_requisicao)));
            $tempoDesdeUltimoPagamento = $machine->ultimo_pagamento_recebido
                ? abs($this->tempoOffline(Carbon::parse($machine->ultimo_pagamento_recebido)))
                : PHP_INT_MAX;

            $status = $tempoDesdeUltimaRequisicao > 30 ? 'OFFLINE' : 'ONLINE';

            if ($status === 'ONLINE' && $tempoDesdeUltimoPagamento < 30) {
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

    private function createPayment(string $machineId, string $value, string $clientId): void
    {
        Pagamento::create([
            'maquina_id' => $machineId,
            'valor' => $value,
            'mercadoPagoId' => 'CRÉDITO REMOTO',
            'tipo' => 'remote_credit',
            'data' => now(),
            'estornado' => false,
            'cliente_id' => $clientId
        ]);
    }
}
