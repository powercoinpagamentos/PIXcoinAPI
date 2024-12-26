<?php

namespace App\Services;

use App\Services\Interfaces\IPayment;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Str;

class PaymentService implements IPayment
{
    /**
     * @throws GuzzleException
     */
    public function getPaymentFromMP(string $clientToken, string $paymentId): array
    {
        $client = new Client([
            'verify' => false,
        ]);

        $response = $client->get("https://api.mercadopago.com/v1/payments/$paymentId", [
            'headers' => [
                'Authorization' => 'Bearer ' . $clientToken,
            ],
        ]);

        $body = $response->getBody();
        return json_decode($body, true);
    }

    /**
     * @throws GuzzleException
     */
    public function reverse(
        string $mercadoPagoId,
        string $clientToken,
    )
    {
        $client = new Client([
            'verify' => false,
        ]);

        $response = $client->post("https://api.mercadopago.com/v1/payments/$mercadoPagoId/refunds", [
            'headers' => [
                'Authorization' => 'Bearer ' . $clientToken,
                'X-Idempotency-Key' => Str::random(32)
            ],
        ]);

        $body = $response->getBody();
        return json_decode($body, true);
    }
}
