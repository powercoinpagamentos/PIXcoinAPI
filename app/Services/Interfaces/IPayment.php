<?php

namespace App\Services\Interfaces;

interface IPayment
{
    public function getPaymentFromMP(string $clientToken, string $paymentId);
    public function reverse(string $mercadoPagoId, string $clientToken);
    public function getPaymentFromPagBank(string $notificationCode, string $clientEmail, string $clientToken);
}
