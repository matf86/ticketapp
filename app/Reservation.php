<?php

namespace App;


class Reservation
{
    protected $tickets, $email;

    public function __construct($tickets, $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function complete($paymentGateway, $paymentToken)
    {
        $charge = $paymentGateway->charge($this->totalCost(), $paymentToken);

        return Order::forTickets($this->email(), $this->tickets(), $charge);
    }

    public function cancel()
    {
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function email()
    {
        return $this->email;
    }
}