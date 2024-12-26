<?php

namespace App\Http\Controllers;

use App\Actions\Payment\ReceiptPayment;
use App\Actions\Payment\ReceiptPaymentCash;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController
{
    /**
     * @throws GuzzleException
     */
    public function receiptPayment(Request $request, string $customerId): JsonResponse
    {
        $mercadoPagoId = $request->query('id');
        return (new ReceiptPayment($customerId, $mercadoPagoId))->run();
    }

    public function receiptPaymentCash(Request $request, string $machineId): JsonResponse
    {
        $value = $request->query('valor');
        return (new ReceiptPaymentCash($machineId, $value))->run();
    }
}
