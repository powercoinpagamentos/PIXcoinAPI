<?php

namespace App\Actions\Report;

use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

readonly class PaymentsRefundsReport
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

        $totalValor = Pagamento::query()
            ->where('maquina_id', $this->machineId)
            ->where('estornado', true)
            ->whereBetween('data', [$startDate, $endDate])
            ->sum('valor');

        return response()->json(['valor' => $totalValor]);
    }
}
