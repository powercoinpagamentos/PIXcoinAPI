<?php

namespace App\Actions\Payment;

use App\Helpers\PaymentHelper;
use App\Models\Maquina;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

readonly class GetPayments
{
    public function __construct(private string $machineId)
    {
    }

    public function run(): JsonResponse
    {
        DB::beginTransaction();

        try {
            $machine = $this->getMachine();

            if (!$machine) {
                DB::rollBack();
                DB::disconnect();
                return response()->json(['error' => 'Machine not found'], 404);
            }

            $payments = $machine->pagamentos()->orderBy('data', 'desc')->get();

            $paymentHelper = new PaymentHelper();
            $totalPayment = $paymentHelper->getTotalPayments($payments);

            DB::commit();
            DB::disconnect();

            return response()->json([
                'total' => $totalPayment['totalSemEstorno'] ?? 0,
                'estornos' => $totalPayment['totalComEstorno'] ?? 0,
                'cash' => $totalPayment['totalEspecie'] ?? 0,
                'estoque' => $machine->estoque ?? '--',
                'pagamentos' => $payments
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            DB::disconnect();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private function getMachine(): ?Maquina
    {
        return Maquina::query()
            ->with(['pagamentos' => function($query) {
                $query->orderBy('data', 'desc');
            }])
            ->find($this->machineId);
    }
}
