<?php


class OrderFactory
{
    public static function createForConcert($concert, $ticketQuantity = 1, $overrides = [])
    {
        $order = factory(\App\Order::class)->create($overrides);
        $tickets = factory(\App\Ticket::class, $ticketQuantity)->create(['concert_id' => $concert->id]);

        $order->tickets()->saveMany($tickets);

        return $order;
    }
}