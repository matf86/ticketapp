<?php

namespace App\Billing;


interface PaymentGatewayInterface
{
    public function charge($amount, $token, $account_id);

    public function getValidTestToken($cardNumber);

    public function newChargesDuring($callback);
}