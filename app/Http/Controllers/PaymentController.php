<?php

namespace App\Http\Controllers;

use App\Actions\Payment\ReceiptPayment;
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
}
