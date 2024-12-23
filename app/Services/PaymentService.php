<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class PaymentService
{
    /**
     * @throws GuzzleException
     */
    public function getPaymentFromMP(string $clientToken, $paymentId): array
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
}
