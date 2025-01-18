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
            'totalCreditoRemoto' => 0
        ];

        foreach ($paymentsFromMachine as $payment) {
            $valor = floatval($payment->valor) ?: 0;

            if ($payment->estornado) {
                $totais['totalComEstorno'] += $valor;
            } else {
                $totais['totalSemEstorno'] += $valor;
            }

            if ($payment->mercadoPagoId === 'CASH') {
                $totais['totalEspecie'] += $valor;
            }

            if($payment->mercadoPagoId === 'CRÉDITO REMOTO') {
                $totais['totalCreditoRemoto'] += $valor;
            }
        }

        return $totais;
    }
}
