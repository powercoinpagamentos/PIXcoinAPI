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

        $customer = $this->getCustomer();
        if (!$customer) {
            return response()->json(['error' => 'Cliente não encontrado', 'pago' => false], 404);
        }

        $maxAttempts = 10;
        $attempt = 0;
        $payment = null;

        do {
            $attempt++;

            $payment = $this->getPaymentsFromMP($customer->mercadoPagoToken);

            if ($payment['status'] === 'approved') {
                break;
            }

            if ($payment['status'] !== 'pending') {
                return new JsonResponse([
                    'message' => "Pagamento com status: " . $payment['status'],
                    'pago' => false
                ]);
            }

        } while ($attempt < $maxAttempts);

        if ($payment['status'] !== 'approved') {
            return new JsonResponse([
                'message' => "Pagamento ainda não aprovado após várias tentativas",
                'pago' => false
            ]);
        }

        $storeId = $payment['store_id'] ?? '';
        $value = $payment['transaction_amount'] ?? '';
        $paymentType = $payment['payment_type_id'] ?? '';
        $operationTax = $this->getOperationTax($payment);

        $machine = $this->getMachine($customer, $storeId);

        if (!$storeId || is_null($machine)) {
            return new JsonResponse(['message' => "Máquina não possui store id cadastrado ou esse pagamento não é de uma máquina", 'pago' => true]);
        }

        if ((bool)$machine->disabled) {
            return $this->reversal(
                $customer->mercadoPagoToken,
                $machine->id ?? '',
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
                $customer->mercadoPagoToken,
                $machine->id ?? '',
                $value,
                $paymentType,
                'Máquina offline'
            );
        }

        if ($this->lesserThanMinTicket($value, $machine->valorDoPulso)) {
            return $this->reversal(
                $customer->mercadoPagoToken,
                $machine->id ?? '',
                $value,
                $paymentType,
                'Valor abaixo do preço da máquina'
            );
        }

        try {
            DB::transaction(function () use ($machine, $value, $paymentType, $operationTax) {
                $this->createPayment(
                    $machine->id,
                    $value,
                    false,
                    $paymentType,
                    $this->customerId,
                    $operationTax
                );

                $this->updateMachine($machine->id, $value);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao processar transação: ' . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao processar a transação.'], 500);
        }

        return response()->json(['message' => 'Novo pagamento registrado!', 'pago' => true]);
    }

    private function getCustomer(): ?Cliente
    {
        return Cliente::with('maquinas')->find($this->customerId);
    }

    private function getPaymentsFromMP($clientToken): array
    {
        return $this->paymentService->getPaymentFromMP($clientToken, $this->mercadoPagoId);
    }

    private function getOperationTax(array  $payment): string
    {
        if (isset($payment['fee_details']) && is_array($payment['fee_details']) && count($payment['fee_details']) > 0) {
            return $payment['fee_details'][0]['amount'] ?? '';
        }

        return '';
    }

    private function getMachine(Cliente $customer, string $storeId): ?Maquina
    {
        return $customer->maquinas()->with('pagamentos')->get()->first(function(Maquina $maquina) use ($storeId) {
            return $maquina->store_id === $storeId;
        });
    }

    private function reversal(
        string $clientToken,
        string $machineId,
        string $amount,
        string $paymentType,
        string $reasonReversed
    ): JsonResponse
    {
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
        string $amount,
        bool $reversed,
        string $paymentType,
        string $clientId,
        ?string $tax = '',
        ?string $reasonReversed = ''
    ): void
    {
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

    private function updateMachine(string $machineId, string $value): void
    {
        Maquina::query()
            ->where('id', $machineId)
            ->update([
                'valor_do_pix' => $value,
                'ultimo_pagamento_recebido' => now()
            ]);
    }

    private function machineOffline(Maquina $machine): bool
    {
        $tempoDesdeUltimaRequisicao = abs($this->tempoOffline(Carbon::parse($machine->ultima_requisicao)));
        return $tempoDesdeUltimaRequisicao > 60;
    }

    private function tempoOffline(Carbon $data): int
    {
        return Carbon::now()->diffInSeconds($data);
    }

    private function lesserThanMinTicket(float $value, float $ticketMin): bool
    {
        return $value < $ticketMin;
    }

    private function existingPayment(Maquina $machine): bool
    {
        return $machine->pagamentos()->where('mercadoPagoId', $this->mercadoPagoId)->exists();
    }

    private function handleTramoia(): void
    {
        Pagamento::where('mercadoPagoId', $this->mercadoPagoId)
            ->update([
                'motivo_estorno' => 'Tentativa de Golpe',
                'estornado' => true,
            ]);
    }
}
