<?php

namespace App\Http\Controllers;

use App\Actions\Payment\CreatePaymentOrderPagBank;
use App\Actions\Payment\GenerateQRCode;
use App\Actions\Payment\ReceiptPayment;
use App\Actions\Payment\ReceiptPaymentCash;
use App\Actions\Payment\ReceiptPaymentFromPagBank;
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

    public function testMercadoPago(Request $request): JsonResponse
    {
        if ($request->query('data_id') === '123456' && $request->query('type') === 'payment') {
            return response()->json(['status' => 'ok']);
        }

        return response()->json(['error' => 'Parâmetros inválidos'], 400);
    }

    /**
     * @throws GuzzleException
     */
    public function receiptPaymentFromPagBank(Request $request, string $clientId): JsonResponse
    {
        $notificationCode = $request->get('notificationCode');

        return (new ReceiptPaymentFromPagBank($clientId, $notificationCode))->run();
    }

    public function qrCodeGenerator(Request $request, string $clientId, string $machineId): JsonResponse
    {
        $value = $request->get('valor');
        if (!$value) {
            return new JsonResponse(['status' => 'Valor não informado ou inválido!'], 400);
        }

        return (new GenerateQRCode($clientId, $machineId, $value))->run();
    }

    public function createPaymentOrderPagBank(Request $request, string $clientId): JsonResponse
    {
        $paymentValue = $request->get('valor');
        if (!$paymentValue) {
            return new JsonResponse(['status' => 'Valor não informado ou inválido!'], 400);
        }

        return (new CreatePaymentOrderPagBank($paymentValue , $clientId))->run();
    }
}
