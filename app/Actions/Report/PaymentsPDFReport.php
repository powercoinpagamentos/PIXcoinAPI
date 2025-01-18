<?php

namespace App\Actions\Report;

use App\Helpers\PaymentHelper;
use App\Models\Maquina;
use Carbon\Carbon;

readonly class PaymentsPDFReport
{
    public function __construct(
        private string $machineId,
        private string $startDate,
        private string $endDate,
    )
    {
    }

    public function run(): array
    {
        $startDate = Carbon::parse($this->startDate)->startOfDay();
        $endDate = Carbon::parse($this->endDate)->endOfDay();

        $maquina = Maquina::query()
            ->with(['pagamentos', 'cliente'])
            ->find($this->machineId);

        $payments = $maquina->pagamentos
            ->where('maquina_id', $this->machineId)
            ->whereBetween('data', [$startDate, $endDate]);

        $totalPayments = (new PaymentHelper())->getTotalPayments($payments);

        $adjustedPayment = $this->transformPaymentsData($payments->toArray());
        $filteredValuesArray = array_map(function($item) {
            return array_values(array_filter($item, fn($value) => $value !== ''));
        }, $adjustedPayment);

        $tableArray = [
            'headers' => ['Data', 'Pagamento', 'Valor', 'Ident.MP', 'Estornado'],
            'rows' => $filteredValuesArray,
        ];

        return [
            'maquinaNome' => $maquina->nome,
            'clienteNome' => $maquina->cliente->nome,
            'totalSemEstorno' => $totalPayments['totalSemEstorno'],
            'totalComEstorno' => $totalPayments['totalComEstorno'],
            'totalEspecie' => $totalPayments['totalEspecie'],
            'totalCreditoRemoto' => $totalPayments['totalCreditoRemoto'],
            'tableArray' => $tableArray,
            'startDate' => $startDate,
            'endDate' => $endDate
        ];
    }

    private function retrieveFormattedDate(string $isoDate, string $format = "d/m/Y H:i:s"): string
    {
        $date = Carbon::parse($isoDate);
        return $date->format($format);
    }

    private function retrievePaymentForm(string $currentPaymentForm): string
    {
        $paymentFormMap = [
            'bank_transfer' => 'PIX',
            'CASH' => 'Especie',
            'debit_card' => 'Débito',
            'credit_card' => 'Crédito',
            'account_money' => '',
            'remote_credit' => 'Crédito Remoto'
        ];

        return $paymentFormMap[$currentPaymentForm] ?? '';
    }

    private function formatToBRL(string $value): string
    {
        $number = floatval($value);
        return 'R$ ' . number_format($number, 2, ',', '.');
    }

    private function retrieveReversedText(bool $reversed): string
    {
        return $reversed ? 'Estornado' : 'Recebido';
    }

    private function transformPaymentsData(array $payments): array
    {
        return array_map(function ($payment) {
            return [
                'date' => $this->retrieveFormattedDate($payment['data']),
                'paymentForm' => $this->retrievePaymentForm($payment['tipo']),
                'value' => $this->formatToBRL($payment['valor']),
                'identifierMP' => $payment['mercadoPagoId'],
                'reversed' => $this->retrieveReversedText($payment['estornado']),
            ];
        }, $payments);
    }
}
