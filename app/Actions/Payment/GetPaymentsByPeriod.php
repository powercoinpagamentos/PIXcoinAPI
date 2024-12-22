<?php

namespace App\Actions\Payment;

use App\Helpers\PaymentHelper;
use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

readonly class GetPaymentsByPeriod
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

        $paymentsByRange = Pagamento::query()
            ->where('maquina_id', $this->machineId)
            ->whereBetween('data', [$startDate, $endDate])
            ->get();

        $totalPayments = (new PaymentHelper())->getTotalPayments($paymentsByRange);

        return response()->json([
            'total' => $totalPayments['totalSemEstorno'],
            'estornos' => $totalPayments['totalComEstorno'],
            'cash' => $totalPayments['totalEspecie'],
            'pagamentos' => $paymentsByRange
        ]);
    }
}
