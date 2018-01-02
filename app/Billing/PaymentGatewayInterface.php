<?php

namespace App\Billing;


interface PaymentGatewayInterface
{
    public function charge($amount, $token);

    public function getValidTestToken($cardNumber);

    public function newChargesDuring($callback);
}