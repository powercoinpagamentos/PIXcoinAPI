<?php

namespace App\Actions\Report;

use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

readonly class PaymentsTaxReport
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

        $calculateTotalFees = function (string $type) use ($startDate, $endDate): float {
            return Pagamento::query()
                ->where('maquina_id', $this->machineId)
                ->where('tipo', $type)
                ->where('estornado', false)
                ->whereBetween('data', [$startDate, $endDate])
                ->sum('taxas') ?? 0.0;
        };

        $totalTaxasPix = $calculateTotalFees('bank_transfer') + ($calculateTotalFees('account_money') ?? 0);
        $totalTaxasCredito = $calculateTotalFees('credit_card');
        $totalTaxasDebito = $calculateTotalFees('debit_card');

        return response()->json([
            'pix' => $totalTaxasPix,
            'credito' => $totalTaxasCredito,
            'debito' => $totalTaxasDebito,
        ]);
    }
}
