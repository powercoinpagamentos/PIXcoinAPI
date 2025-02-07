<?php

namespace App\Services\Interfaces;

interface IPayment
{
    public function getPaymentFromMP(string $clientToken, string $paymentId);
    public function createPaymentIntentionMP(string $token, array $paymentData);
    public function reversePaymentFromMP(string $mercadoPagoId, string $clientToken);
    public function getPaymentFromPagBank(string $notificationCode, string $clientEmail, string $clientToken);
    public function reversePaymentFromPagBank(string $email, string $token, string $operationId);
}
