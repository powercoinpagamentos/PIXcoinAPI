<?php

namespace App\Actions\Payment;

use App\Models\Cliente;
use App\Services\PaymentService;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Http\JsonResponse;

readonly class ReceiptPayment
{
    public function __construct(private string $customerId, private string $mercadoPagoId)
    {
    }

    /**
     * @throws GuzzleException
     */
    public function run(): JsonResponse
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return response()->json(['error' => 'Cliente não encontrado'], 404);
        }

        $payment = $this->getPaymentsFromMP($customer->mercado_pago_token);
        dd($payment);
    }

    private function getCustomer(): ?Cliente
    {
        return Cliente::with('maquinas')->find($this->customerId);
    }

    /**
     * @throws GuzzleException
     */
    private function getPaymentsFromMP($clientToken): array
    {
        return (new PaymentService())->getPaymentFromMP($clientToken, $this->mercadoPagoId);
    }
}
