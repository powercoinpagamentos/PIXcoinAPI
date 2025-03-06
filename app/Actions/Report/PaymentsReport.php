<?php

namespace App\Actions\Report;

use App\Models\Pagamento;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

readonly class PaymentsReport
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

        $tiposPagamento = ['bank_transfer', 'credit_card', 'debit_card', 'account_money'];

        $pagamentos = Pagamento::query()
            ->where('maquina_id', $this->machineId)
            ->where('estornado', false)
            ->whereBetween('data', [$startDate, $endDate])
            ->whereIn('tipo', $tiposPagamento)
            ->get(['tipo', 'valor']);

        $totaisPorTipo = $pagamentos->groupBy('tipo')
            ->map(fn($items) => $items->sum('valor'));

        $totalPIX = ($totaisPorTipo['bank_transfer'] ?? 0) + ($totaisPorTipo['account_money'] ?? 0);

        return response()->json([
            'pix' => $totalPIX ?? 0,
            'especie' => -1,
            'credito' => $totaisPorTipo['credit_card'] ?? 0,
            'debito' => $totaisPorTipo['debit_card'] ?? 0,
        ]);
    }
}
