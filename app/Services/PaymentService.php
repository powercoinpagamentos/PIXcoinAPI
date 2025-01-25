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

    /**
     * @throws GuzzleException
     */
    public function getPaymentFromPagBank(string $notificationCode, string $clientEmail, string $clientToken)
    {
        $client = new Client([
            'verify' => false,
        ]);

        $uri = env('PAGSEGURO_API_URL');
        $completeURI = "$uri/$notificationCode?email=$clientEmail&token=$clientToken";

        file_put_contents(storage_path('antes.txt'), "ANTES DO REQUEST", FILE_APPEND);
        $response = $client->request('GET', $completeURI, [
            'headers' => [
                'content-type' => 'application/json',
            ],
        ]);

        $data = json_decode($response->getBody(), true);
        file_put_contents(storage_path('pagseguro_respostas.txt'), $response->getBody(), FILE_APPEND);

        return $data;
    }
}
