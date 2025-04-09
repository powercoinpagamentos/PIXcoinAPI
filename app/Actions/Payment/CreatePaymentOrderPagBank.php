<?php
declare(strict_types=1);

namespace App\Actions\Payment;

use App\Models\Cliente;
use App\Services\Interfaces\IPayment;
use Illuminate\Http\JsonResponse;

readonly class CreatePaymentOrderPagBank
{
    private IPayment $paymentService;

    public function __construct(
        private string $paymentValue,
        private string $clientId
    )
    {
        $this->paymentService = resolve(IPayment::class);
    }

    public function run(): JsonResponse
    {
        $client = $this->getClient();
        if (empty($client)) {
            return new JsonResponse(['status' => 'Cliente não encontrado'], 404);
        }

        $data = [
            'reference_id' => $this->clientId,
            'customer' => [
                'tax_id' => '12345678909',
                'name' => 'A A',
                'email' => 'a@a.com',
            ],
            'qr_codes' => [
                [
                    'amount' => [
                        'value' => $this->paymentValue,
                    ],
                ]
            ],
            'notification_urls' => [
                'https://meusite.com/notificacoes'
            ]
        ];

        try {
            $response = $this->paymentService->createPaymentOrderPagBank(
                $data,
                $this->clientId,
                $client->pagbankToken
            );

            return new JsonResponse(
                [
                    'qr_codes' => $response['qr_codes'][0]['links'],
                    'client_token' => $client->pagbankToken,
                    'order_id' => $response['id'],
                ],
                200
            );
        } catch (\Exception $exception) {
            return new JsonResponse(['erro' => $exception->getMessage()], 500);
        }
    }

    private function getClient(): ?Cliente
    {
        return Cliente::query()->find($this->clientId);
    }
}
