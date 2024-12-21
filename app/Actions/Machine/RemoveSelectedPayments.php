<?php

namespace App\Actions\Machine;

use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

readonly class RemoveSelectedPayments
{
    public function __construct(
        private string $machineId,
        private string $startDate,
        private string $endDate,
    )
    {
    }

    public function run(): JsonResponse
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $paymentsRemoved = Pagamento::query()
            ->where('maquina_id', $this->machineId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->delete();

        return response()->json([
            'message' => "Foram removido(s) $paymentsRemoved pagamento(s) da máquina no período de {$startDate->format('d/m/Y')} até {$endDate->format('d/m/Y')}."
        ]);
    }
}
