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
    public function getPaymentFromPagBank(string $notificationCode, string $clientEmail, string $clientToken): array
    {
        $client = new Client([
            'verify' => false,
        ]);

        $uri = env('PAGSEGURO_API_URL');
        $completeURI = "$uri/$notificationCode?email=$clientEmail&token=$clientToken";

        $response = $client->request('GET', $completeURI, [
            'headers' => [
                'content-type' => 'application/json',
            ],
        ]);

        $xmlContent = $response->getBody();
        $xmlObject = simplexml_load_string($xmlContent);

        return json_decode(json_encode($xmlObject), true);
    }

    /**
     * @throws GuzzleException
     */
    public function reversePaymentFromPagBank(string $email, string $token, string $operationId)
    {
        $client = new Client([
            'verify' => false,
        ]);

        $response = $client->post('https://ws.pagseguro.uol.com.br/v2/transactions/refunds', [
            'form_params' => [
                'email' => $email,
                'token' => $token,
                'transactionCode' => $operationId,
            ],
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
        ]);

        $body = $response->getBody();
        return json_decode($body, true);
    }

    /**
     * @throws GuzzleException
     */
    public function createPaymentIntention(string $token, array $paymentData)
    {
        $client = new Client([
            'verify' => false,
        ]);

        $response = $client->request('POST', 'https://api.mercadopago.com/v1/payments', [
            'headers' => [
                'content-type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ],
            'json' => $paymentData,
        ]);

        $body = $response->getBody();
        return json_decode($body, true);
    }
}
