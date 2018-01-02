<?php

namespace App;

use App\Billing\Charge;
use App\Facades\OrderConfirmationNumber;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = [];

    public static function forTickets($email, $tickets, Charge $charge)
    {
        $order = self::create([
            'confirmation_number' => OrderConfirmationNumber::generate(),
            'email' => $email,
            'amount' => $charge->amount(),
            'card_last_four' => $charge->cardLastFour()
        ]);


        $tickets->each->claimFor($order);

        return $order;
    }

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }


    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function ticketsQuantity()
    {
        return $this->tickets()->count();
    }

    public static function findByConfirmationNumber($number)
    {
        return self::where('confirmation_number', $number)->firstOrFail();
    }

    public function toArray()
    {
        return [
            'confirmation_number' => $this->confirmation_number,
            'email' => $this->email,
            'amount' => $this->amount,
            'tickets' => $this->tickets->map(function($ticket) {
                return ['code' => $ticket->code];
            })->all()
        ];
    }
}
