<?php

namespace App\Actions\Payment;

use App\Models\Cliente;
use App\Models\Maquina;
use App\Models\Pagamento;
use App\Services\Interfaces\IDiscord;
use App\Services\Interfaces\IPayment;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
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

    /**
     * @throws GuzzleException
     */
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

        $payment = $this->getPaymentsFromMP($customer->mercadoPagoToken);

        if ($payment['status'] !== 'approved') {
            return new JsonResponse(['message' => "Pagamento ainda não realizado", 'pago' => false]);
        }

        $externalReference = isset($payment['external_reference']) ? (string) $payment['external_reference'] : "";

        $storeId = $payment['store_id'] ?? '';
        $value = $payment['transaction_amount'] ?? '';
        $paymentType = $payment['payment_type_id'] ?? '';
        $operationTax = $this->getOperationTax($payment);

        $machine = $this->getMachine($customer, $storeId, $externalReference);

        $paymentAlreadyExists = Pagamento::where('maquina_id', $machine->id)
            ->where('mercadoPagoId', $this->mercadoPagoId)
            ->exists();

        if ($paymentAlreadyExists) {
            return new JsonResponse(['message' => "Pagamento já realizado.", 'pago' => true]);
        }

        if (!$machine || (bool)$machine->disabled) {
            return $this->reversal(
                $customer->mercadoPagoToken,
                $machine->id ?? '',
                $value,
                $paymentType,
                'Máquina desabilitada'
            );
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

        if ($this->existingPayment($machine)) {
            return response()->json(['error' => 'Esse pagamento já existe na base.', 'pago' => false], 409);
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

        $this->notifierDiscord($value, $customer->nome, $machine->nome);

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

    private function getMachine(Cliente $customer, string $storeId, string $externalReference): ?Maquina
    {
        return $customer->maquinas()->with('pagamentos')->get()->first(function(Maquina $maquina) use ($storeId, $externalReference) {
            if ($externalReference) {
                return $maquina->id === $externalReference;
            }

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
        $this->paymentService->reverse($this->mercadoPagoId, $clientToken);
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
        return $tempoDesdeUltimaRequisicao > 5;
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
        return $machine->pagamentos->contains(function ($pagamento) {
            return $pagamento->mercadoPagoId === $this->mercadoPagoId;
        });
    }

    private function handleTramoia(): void
    {
        Pagamento::where('mercadoPagoId', $this->mercadoPagoId)
            ->update([
                'motivo_estorno' => 'Tentativa de Golpe',
                'estornado' => true,
            ]);
    }

    private function notifierDiscord(
        string $value,
        string $clientName,
        string $machineName,
    ): void
    {
        /** @var IDiscord $discordAPI */
        $discordAPI = resolve(IDiscord::class);

        $discordAPI->notificar(
            env('NOTIFICACOES_PAGAMENTOS'),
            "Novo pagamento recebido no Mercado Pago. R$ $value",
            "Cliente $clientName - Máquina: $machineName",
        );
    }
}
