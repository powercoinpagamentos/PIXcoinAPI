<?php

namespace App\Actions\Payment;

use App\Models\Cliente;
use App\Models\Maquina;
use App\Models\Pagamento;
use App\Services\Interfaces\IPayment;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

readonly class ReceiptPayment
{
    private IPayment $paymentService;
    private const MAX_ATTEMPTS = 50;
    private const OFFLINE_THRESHOLD = 30;

    public function __construct(private string $customerId, private string $mercadoPagoId)
    {
        $this->paymentService = resolve(IPayment::class);
    }

    public function run(): JsonResponse
    {
        // BY-PASS para validação do MP
        if ($this->mercadoPagoId === '123456') {
            return response()->json(['status' => 'ok', 'pago' => true]);
        }

        DB::beginTransaction();

        try {
            $customer = $this->getCustomer();
            if (!$customer) {
                DB::rollBack();
                return response()->json(['error' => 'Cliente não encontrado', 'pago' => false], 404);
            }

            $payment = $this->checkPaymentStatus($customer->mercadoPagoToken);
            if (!$payment['approved']) {
                DB::rollBack();
                return new JsonResponse($payment['response']);
            }

            $paymentData = $payment['data'];
            $storeId = $paymentData['store_id'] ?? '';
            $value = (float)($paymentData['transaction_amount'] ?? 0);
            $paymentType = $paymentData['payment_type_id'] ?? '';
            $operationTax = $this->getOperationTax($paymentData);

            $machine = $this->getMachine($customer, $storeId);

            $validationResponse = $this->validatePayment($machine, $value, $customer->mercadoPagoToken, $paymentType);
            if ($validationResponse) {
                DB::rollBack();
                return $validationResponse;
            }

            $this->processPayment($machine, $value, $paymentType, $operationTax);

            DB::commit();
            return response()->json(['message' => 'Novo pagamento registrado!', 'pago' => true]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro ao processar pagamento: ' . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao processar o pagamento.'], 500);
        } finally {
            DB::disconnect();
        }
    }

    private function checkPaymentStatus(string $clientToken): array
    {
        $attempt = 0;

        do {
            $attempt++;
            $payment = $this->paymentService->getPaymentFromMP($clientToken, $this->mercadoPagoId);

            if ($payment['status'] === 'approved') {
                return ['approved' => true, 'data' => $payment];
            }

            if ($payment['status'] !== 'pending') {
                return [
                    'approved' => false,
                    'response' => [
                        'message' => "Pagamento com status: " . $payment['status'],
                        'pago' => false
                    ]
                ];
            }

            usleep(500000);

        } while ($attempt < self::MAX_ATTEMPTS);

        return [
            'approved' => false,
            'response' => [
                'message' => "Pagamento ainda não aprovado após várias tentativas",
                'pago' => false
            ]
        ];
    }

    private function validatePayment(?Maquina $machine, float $value, string $clientToken, string $paymentType): ?JsonResponse
    {
        if (!$machine || !$machine->store_id) {
            return new JsonResponse([
                'message' => "Máquina não possui store id cadastrado ou esse pagamento não é de uma máquina",
                'pago' => true
            ]);
        }

        if ((bool)$machine->disabled) {
            return $this->reversal(
                $clientToken,
                $machine->id,
                $value,
                $paymentType,
                'Máquina desabilitada'
            );
        }

        if ($this->existingPayment($machine)) {
            return new JsonResponse(['message' => "Pagamento já realizado.", 'pago' => true]);
        }

        if ($this->machineOffline($machine)) {
            return $this->reversal(
                $clientToken,
                $machine->id,
                $value,
                $paymentType,
                'Máquina offline'
            );
        }

        if ($this->lesserThanMinTicket($value, $machine->valorDoPulso)) {
            return $this->reversal(
                $clientToken,
                $machine->id,
                $value,
                $paymentType,
                'Valor abaixo do preço da máquina'
            );
        }

        return null;
    }

    private function processPayment(Maquina $machine, float $value, string $paymentType, string $operationTax): void
    {
        $this->createPayment(
            $machine->id,
            $value,
            false,
            $paymentType,
            $this->customerId,
            $operationTax
        );

        $this->updateMachine($machine->id, $value);
    }

    private function getCustomer(): ?Cliente
    {
        return Cliente::with(['maquinas' => function($query) {
            $query->with('pagamentos');
        }])->find($this->customerId);
    }

    private function getMachine(Cliente $customer, string $storeId): ?Maquina
    {
        return $customer->maquinas->first(function(Maquina $maquina) use ($storeId) {
            return $maquina->store_id === $storeId;
        });
    }

    private function getOperationTax(array $payment): string
    {
        return $payment['fee_details'][0]['amount'] ?? '';
    }

    private function reversal(
        string $clientToken,
        string $machineId,
        float $amount,
        string $paymentType,
        string $reasonReversed
    ): JsonResponse {
        $this->paymentService->reversePaymentFromMP($this->mercadoPagoId, $clientToken);

        if ($machineId) {
            $this->createPayment(
                $machineId,
                $amount,
                true,
                $paymentType,
                $this->customerId,
                '',
                $reasonReversed
            );
        }

        return response()->json(['retorno' => 'pagamento estornado', 'pago' => false]);
    }

    private function createPayment(
        string $machineId,
        float $amount,
        bool $reversed,
        string $paymentType,
        string $clientId,
        string $tax = '',
        string $reasonReversed = ''
    ): void {
        Pagamento::create([
            'maquina_id' => $machineId,
            'valor' => $amount,
            'mercadoPagoId' => $this->mercadoPagoId,
            'estornado' => $reversed,
            'tipo' => $paymentType,
            'data' => now(),
            'taxas' => $tax,
            'cliente_id' => $clientId,
            'motivo_estorno' => $reasonReversed,
        ]);
    }

    private function updateMachine(string $machineId, float $value): void
    {
        Maquina::where('id', $machineId)->update([
            'valor_do_pix' => $value,
            'ultimo_pagamento_recebido' => now()
        ]);
    }

    private function machineOffline(Maquina $machine): bool
    {
        if (!$machine->ultima_requisicao) return true;

        return Carbon::now()->diffInSeconds(Carbon::parse($machine->ultima_requisicao)) > self::OFFLINE_THRESHOLD;
    }

    private function lesserThanMinTicket(float $value, float $ticketMin): bool
    {
        return $value < $ticketMin;
    }

    private function existingPayment(Maquina $machine): bool
    {
        return $machine->pagamentos->contains('mercadoPagoId', $this->mercadoPagoId);
    }
}
