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

readonly class ReceiptPaymentFromPagBank
{
    private IPayment $paymentService;

    public function __construct(
        private string $clientId,
        private string|int $notificationCode
    )
    {
        $this->paymentService = resolve(IPayment::class);
    }

    /**
     * @throws GuzzleException
     */
    public function run(): JsonResponse
    {
        $client = $this->getClient();

        $clientToken = $client->pagbankToken ?? '';
        $clientEmail = $client->pagbankEmail ?? '';

        $payment = $this->getPayment($clientEmail, $clientToken);

        $operationTax = $payment['creditorFees']['intermediationFeeAmount'];
        $paymentType = $this->resolvePaymentType($payment['paymentMethod']['type']);
        $deviceInfo = $payment['deviceInfo'];
        $value = $payment['items']['item']['amount'];
        $transactionCode = $payment['code'];

        $machine = $client
            ->maquinas()
            ->where('maquininha_serial', $deviceInfo['serialNumber'])
            ->first();

        if (!$machine | $machine->disabled) {
            return $this->reversePayment(
                $clientEmail,
                $clientToken,
                $transactionCode,
                $machine->id ?? '',
                $value,
                $client->id,
                'Máquina inexistente ou desabilitada',
                $paymentType
            );
        }

        if ($this->machineOffline($machine)) {
            return $this->reversePayment(
                $clientEmail,
                $clientToken,
                $transactionCode,
                $machine->id ?? '',
                $value,
                $client->id,
                'Máquina offline',
                $paymentType
            );
        }

        if ($this->lesserThanMinTicket($value, $machine->valorDoPulso)) {
            return $this->reversePayment(
                $clientEmail,
                $clientToken,
                $transactionCode,
                $machine->id ?? '',
                $value,
                $client->id,
                'Valor do pagamento abaixo do valor da máquina',
                $paymentType
            );
        }

        try {
            DB::transaction(function () use (
                $machine,
                $value,
                $paymentType,
                $operationTax,
                $client,
                $transactionCode
            ) {
                $this->createPayment(
                    $machine->id,
                    $value,
                    false,
                    $paymentType,
                    $client->id,
                    $transactionCode,
                    $operationTax,
                );

                $this->updateMachine($machine->id, $value);
            });
        } catch (\Exception $e) {
            Log::error('Erro ao processar transação: ' . $e->getMessage());
            return response()->json(['message' => 'Erro interno ao processar a transação.'], 500);
        }

        $this->notifierDiscord($value, $client->nome, $machine->nome);

        return response()->json(['message' => 'Novo pagamento registrado!']);
    }

    private function getClient(): Cliente
    {
        return Cliente::find($this->clientId);
    }

    private function createPayment(
        string $machineId,
        string $amount,
        bool $reversed,
        string $paymentType,
        string $clientId,
        ?string $code = '',
        ?string $tax = '',
        ?string $reasonReversed = ''
    ): void
    {
        Pagamento::create([
            'maquina_id' => $machineId,
            'valor' => $amount,
            'mercadoPagoId' => $code,
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

    /**
     * @throws GuzzleException
     */
    private function getPayment(string $clientEmail, string $clientToken): array
    {
        return $this->paymentService->getPaymentFromPagBank(
            $this->notificationCode,
            $clientEmail,
            $clientToken
        );
    }

    private function reversePayment(
        string $email,
        string $token,
        string $transactionCode,
        string $machineId,
        string $value,
        string $clientId,
        string $reasonReversed,
        string $paymentType
    ): JsonResponse
    {
        $this->paymentService->reversePaymentFromPagBank($email, $token, $transactionCode);

        $this->createPayment(
            $machineId,
            $value,
            true,
            $paymentType,
            $clientId,
            $transactionCode,
            '',
            $reasonReversed

        );
        return response()->json(['retorno' => 'pagamento estornado']);
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

    private function resolvePaymentType($paymentTypeId): string
    {
        $map = [
            '8' => 'debit_card',
            '1' => 'credit_card',
            '11' => 'bank_transfer'
        ];

        return $map[$paymentTypeId] ?? '';
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
            "Novo pagamento recebido no PagBank. R$ $value",
            "Cliente $clientName - Máquina: $machineName",
        );
    }
}
