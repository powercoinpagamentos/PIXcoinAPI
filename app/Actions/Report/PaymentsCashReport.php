<?php

namespace App\Actions\Report;

use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

readonly class PaymentsCashReport
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

        $somatorio = Pagamento::query()
            ->where('estornado', false)
            ->where('mercadoPagoId', 'CASH')
            ->where('maquina_id', $this->machineId)
            ->whereBetween('data', [$startDate, $endDate])
            ->sum('valor');

        return response()->json(['valor' => $somatorio]);
    }
}
