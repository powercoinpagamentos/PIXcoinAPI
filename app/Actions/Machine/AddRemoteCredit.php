<?php

namespace App\Actions\Machine;

use App\Models\Maquina;
use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

readonly class AddRemoteCredit
{
    public function __construct(private string $id, private string $value)
    {
    }

    public function run(): JsonResponse
    {
        $machine = $this->getMachine();

        if (!$machine) {
            Log::info("[AddRemoteCredit]: Máquina não encontrada");
            return response()->json(['msg' => 'Falha na operação - Entre em contato com o suporte!'], 400);
        }

        if ($this->machineOffline($machine)) {
            Log::info("[AddRemoteCredit]: Máquina offline. ID: $machine->id - cliente: $machine->cliente_id");
            return response()->json(['msg' => 'MÁQUINA OFFLINE!'], 400);
        }

        if ((float) $this->value < $machine->valorDoPulso) {
            Log::info("[AddRemoteCredit]: Valor abaixo do cadastrado. ID: $machine->id - cliente: $machine->cliente_id");
            return response()->json(['msg' => "Valor do pulso abaixo do configurado. Valor configurado: $machine->valorDoPulso"], 400);
        }

        $machine->update([
            'ultimo_pagamento_recebido' => now(),
            'valor_do_pix' => $this->value,
            'is_remote_credit' => true,
        ]);

        $this->createPayment($machine->id, $this->value, $machine->cliente_id);

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
        if (!$machine->ultima_requisicao) {
            return true;
        }

        $segundosDesdeUltimaRequisicao = $this->tempoOffline(Carbon::parse($machine->ultima_requisicao));
        return $segundosDesdeUltimaRequisicao > 60;
    }

    private function tempoOffline(Carbon $dataUltimaRequisicao): int
    {
        return Carbon::now()->diffInSeconds($dataUltimaRequisicao);
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
