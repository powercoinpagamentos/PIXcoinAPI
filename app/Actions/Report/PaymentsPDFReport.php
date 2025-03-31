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
            $filteredItem = array_filter($item, fn($value) => $value !== '');
            if (isset($filteredItem['identifierMP']) && in_array($filteredItem['identifierMP'], ['JOGADA BÔNUS', 'CRÉDITO REMOTO'])) {
                return [];
            }

            return array_values($filteredItem);
        }, $adjustedPayment);

        $filteredValuesArray = array_filter($filteredValuesArray);

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
            'tableArray' => $tableArray,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'hasPagBank' => $totalPayments['hasPagBank'],
            'pagBankTotais' => $totalPayments['pagBankTotais'],
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
            'account_money' => 'PIX',
            'remote_credit' => 'Crédito Remoto',
            'bonus' => "Jogada Bônus"
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

    private function handleIdentifierMP(string $identifier): string {
        if (strlen($identifier) >= 36) {
            return 'PagSeguro-' . substr($identifier, 0, 8);
        }
        return $identifier;
    }


    private function transformPaymentsData(array $payments): array
    {
        return array_map(function ($payment) {
            return [
                'date' => $this->retrieveFormattedDate($payment['data']),
                'paymentForm' => $this->retrievePaymentForm($payment['tipo']),
                'value' => $this->formatToBRL($payment['valor']),
                'identifierMP' => $this->handleIdentifierMP($payment['mercadoPagoId']),
                'reversed' => $this->retrieveReversedText($payment['estornado']),
            ];
        }, $payments);
    }
}
