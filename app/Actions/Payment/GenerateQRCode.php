<?php
declare(strict_types=1);

namespace App\Actions\Payment;

use App\Models\Cliente;
use App\Services\Interfaces\IPayment;
use Illuminate\Http\JsonResponse;

readonly class GenerateQRCode
{
    private IPayment $paymentService;

    public function __construct(
        private string $clientId,
        private string $machineId,
        private string $value
    )
    {
        $this->paymentService = resolve(IPayment::class);
    }

    public function run(): JsonResponse
    {
        $client = $this->getClient();
        if (!$client) {
            return new JsonResponse(['status' => 'Cliente não encontrado!'], 404);
        }
        if (!$client->mercadoPagoToken) {
            return new JsonResponse(['status' => 'Cliente sem token!'], 403);
        }

        $requestData = [
            'transaction_amount' => floatval($this->value),
            'description' => 'Pagamento via PIX',
            'payment_method_id' => 'pix',
            'payer' => [
                'email' => $client->email,
            ],
            'external_reference' => $this->machineId,
        ];

        $paymentData = $this->paymentService->createPaymentIntentionMP($client->mercadoPagoToken, $requestData);
        $qrCode = $paymentData['point_of_interaction']['transaction_data']['qr_code'];
        $qrCodeBase64 = $paymentData['point_of_interaction']['transaction_data']['qr_code_base64'];

        return new JsonResponse([
           'status' => 'Pagamento PIX criado com sucesso',
           'payment_data' => $paymentData,
           'qr_code' => $qrCode,
           'qr_code_base64' => $qrCodeBase64,
            'external_reference' => $this->machineId
        ]);
    }

    private function getClient(): ?Cliente
    {
        return Cliente::query()->find($this->clientId);
    }
}
