<?php

namespace App\Actions\Machine;

use App\Helpers\PaymentHelper;
use App\Models\Maquina;
use Illuminate\Http\JsonResponse;

readonly class GetPayments
{
    public function __construct(private string $machineId)
    {
    }

    public function run(): JsonResponse
    {
        $machine = $this->getMachine();
        $payments = $machine->pagamentos;
        $totalPayment = (new PaymentHelper())->getTotalPayments($payments);

        return response()->json([
            'total' => $totalPayment['totalSemEstorno'],
            'estornos' => $totalPayment['totalComEstorno'],
            'cash' => $totalPayment['totalEspecie'],
            'estoque' => $machine->estoque ?? '--',
            'pagamentos' => $payments
        ]);
    }

    private function getMachine(): Maquina
    {
        return Maquina::query()
            ->with('pagamentos')
            ->find($this->machineId);
    }
}
