<?php

namespace App\Helpers;

use Illuminate\Support\Collection;

class PaymentHelper
{
    /**
     * @param Collection $paymentsFromMachine
     * @return array{totalSemEstorno: int, totalComEstorno: int, totalEspecie: int}
     */
    public function getTotalPayments(Collection $paymentsFromMachine): array
    {
        $totais = [
            'totalSemEstorno' => 0,
            'totalComEstorno' => 0,
            'totalEspecie' => 0,
            'hasPagBank' => false,
            'pagBankTotais' => []
        ];

        $pagBankTotais = [
            'totalSemEstorno' => 0,
            'totalComEstorno' => 0,
        ];

        foreach ($paymentsFromMachine as $payment) {
            if (strlen($payment->mercadoPagoId) >= 36) {
                $totais['hasPagBank'] = true;
                continue;
            }

            $valor = floatval($payment->valor) ?: 0;

            if ($payment->estornado) {
                $totais['totalComEstorno'] += $valor;
            }

            if (
                (bool)$payment->estornado === false &&
                !in_array($payment->mercadoPagoId, ['JOGADA BÔNUS', 'CRÉDITO REMOTO'])
            ) {
                $totais['totalSemEstorno'] += $valor;
            }

            if ($payment->mercadoPagoId === 'CASH') {
                $totais['totalEspecie'] += $valor;
            }
        }

        if ($totais['hasPagBank']) {
            foreach ($paymentsFromMachine as $payment) {
                if (strlen($payment->mercadoPagoId) < 36) {
                    continue;
                }

                $valor = floatval($payment->valor) ?: 0;

                if ($payment->estornado) {
                    $pagBankTotais['totalComEstorno'] += $valor;
                } else {
                    $pagBankTotais['totalSemEstorno'] += $valor;
                }
            }

            $totais['pagBankTotais'] = $pagBankTotais;
        }

        return $totais;
    }
}
