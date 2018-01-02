<?php

namespace App\Billing;


class FakePaymentGateway implements PaymentGatewayInterface
{
    const TEST_CARD_NUMBER = '4242424242424242';
    protected $charges;
    protected $tokens;
    protected $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens = collect();
    }

    public function charge($amount, $token)
    {
        if($this->beforeFirstChargeCallback !== null) {
            $callback =  $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback= null;
            $callback->__invoke($this);
        }

        if(! $this->tokens->has($token)) {
            throw new PaymentFailedException();
        }

        return $this->charges[] = new Charge([
            'amount' => $amount,
            'card_last_four' => substr($this->tokens[$token], -4)
        ]);
    }

    public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER)
    {
        $token = 'fake-tok_'.str_random(24);

        $this->tokens[$token] = $cardNumber;

        return $token;
    }

    public function totalCharge()
    {
        return $this->charges->map->amount()->sum();
    }

    public function newChargesDuring($callback)
    {
        $latestChargeIndex = $this->charges->count();

        $callback($this);

        return $this->charges->slice($latestChargeIndex)->reverse()->values();
    }

    public function beforeFirstCharge($callback)
    {
        $this->beforeFirstChargeCallback = $callback;
    }
}