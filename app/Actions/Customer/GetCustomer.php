<?php

namespace App\Actions\Customer;

use App\Helpers\PaymentHelper;
use App\Models\Cliente;
use App\Models\Maquina;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection as BaseCollection;

readonly class GetCustomer
{
    public function __construct(private string $id) {}

    public function run(): JsonResponse
    {
        $customer = $this->getCustomer();
        $machinesWithStatus = $this->formatMachines($customer->maquinas);

        $customer->setRelation('maquinas', $machinesWithStatus);

        return response()->json($customer);
    }

    private function getCustomer(): Cliente
    {
        return Cliente::with('maquinas')->findOrFail($this->id);
    }

    private function formatMachines(Collection $machines): BaseCollection
    {
        return $machines->map(fn(Maquina $machine) => $this->formatMachine($machine));
    }

    private function formatMachine(Maquina $machine): array
    {
        $status = $this->determineStatus($machine);

        $totais = (new PaymentHelper())->getTotalPayments(
            $machine->pagamentos()->orderByDesc('data')->get()
        );

        $pagBankTotais = $totais['pagBankTotais'] ?? [
            'totalSemEstorno' => 0,
            'totalComEstorno' => 0
        ];

        $totalSemEstorno = ($totais['totalSemEstorno'] ?? 0) + ($pagBankTotais['totalSemEstorno'] ?? 0);
        $totalComEstorno = ($totais['totalComEstorno'] ?? 0) + ($pagBankTotais['totalComEstorno'] ?? 0);
        $totalEspecie = $totais['totalEspecie'] ?? 0;

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
            'totalComEstorno' => $totalComEstorno,
            'totalEspecie' => $totalEspecie,
            'disabled' => (bool) $machine->disabled,
            'tempoLow' => $machine->tempoLow,
            'tempoHigh' => $machine->tempoHigh,
            'moves' => $machine->moves,
            'bonus' => $machine->bonus,
            'bonusPlay' => (bool) $machine->bonusPlay,
        ];
    }

    private function determineStatus(Maquina $machine): string
    {
        if (!$machine->ultima_requisicao) {
            return 'OFFLINE';
        }

        $secondsSinceLastRequest = Carbon::now()->diffInSeconds(Carbon::parse($machine->ultima_requisicao));
        $secondsSinceLastPayment = $machine->ultimo_pagamento_recebido
            ? Carbon::now()->diffInSeconds(Carbon::parse($machine->ultimo_pagamento_recebido))
            : INF;

        if ($secondsSinceLastRequest > 5) {
            return 'OFFLINE';
        }

        return $secondsSinceLastPayment < 1800 ? 'PAGAMENTO_RECENTE' : 'ONLINE';
    }
}
