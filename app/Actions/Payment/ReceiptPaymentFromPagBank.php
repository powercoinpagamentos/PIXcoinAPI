<?php

namespace App\Actions\Payment;

use App\Models\Cliente;
use App\Services\PaymentService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;

class ReceiptPaymentFromPagBank
{
    public function __construct(
        private string $clientId,
        private string $notificationType,
        private string|int $notificationCode
    )
    {
    }

    /**
     * @throws GuzzleException
     */
    public function run(): JsonResponse
    {
        $client = $this->getClient();

        $clientToken = $client->pagbankToken ?? '';
        $clientEmail = $client->pagbankEmail ?? '';

        $payment = $this->getPayment($clientEmail, $clientToken);

        return response()->json([]);
    }

    private function getClient(): Cliente
    {
        return Cliente::find($this->clientId);
    }

    /**
     * @throws GuzzleException
     */
    private function getPayment(string $clientEmail, string $clientToken): array
    {
        return ((new PaymentService())->getPaymentFromPagBank(
            $this->notificationCode,
            $clientEmail,
            $clientToken
        ));
    }
}
