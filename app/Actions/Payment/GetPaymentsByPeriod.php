<?php

namespace App\Actions\Payment;

use App\Helpers\PaymentHelper;
use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

readonly class GetPaymentsByPeriod
{
    public function __construct(
        private string $machineId,
        private string $startDate,
        private string $endDate,
    ) {}

    public function run(): JsonResponse
    {
        DB::beginTransaction();

        try {
            $startDate = Carbon::parse($this->startDate)->startOfDay();
            $endDate = Carbon::parse($this->endDate)->endOfDay();

            $paymentsByRange = Pagamento::query()
                ->where('maquina_id', $this->machineId)
                ->whereBetween('data', [$startDate, $endDate])
                ->orderBy('data', 'desc')
                ->get();

            $paymentHelper = new PaymentHelper();
            $totalPayments = $paymentHelper->getTotalPayments($paymentsByRange);

            DB::commit();
            DB::disconnect();

            return response()->json([
                'total' => $totalPayments['totalSemEstorno'] ?? 0,
                'estornos' => $totalPayments['totalComEstorno'] ?? 0,
                'cash' => $totalPayments['totalEspecie'] ?? 0,
                'pagamentos' => $paymentsByRange
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            DB::disconnect();
            return response()->json([
                'error' => 'Failed to retrieve payments',
                'details' => $e->getMessage()
            ], 500);
        }
    }
}
