<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Concert extends Model
{
    protected $guarded = [];

    protected $dates = ['date'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return Order::whereIn('id', $this->tickets()->pluck('order_id'));
    }

    public function attendeeMessages()
    {
        return $this->hasMany(AttendeeMessage::class);
    }

    public function tickets()
    {
        return $this->hasMany(Ticket::class);
    }

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:ia');
    }

    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price / 100, 2);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }

    public function isPublished()
    {
        return (bool) $this->published_at;
    }

    public function publish()
    {
        $this->update([
            'published_at' => Carbon::now()
        ]);

        $this->addTickets($this->ticket_quantity);

        return $this;
    }

    public function reserveTickets($quantity, $email)
    {
        $tickets = $this->findTickets($quantity)->each(function($ticket){
            $ticket->reserve();
        });

        return new Reservation($tickets, $email);
    }

    /**
     * @param $ticketsQuantity
     *
     * @return mixed
     * @throws NotEnoughTicketsException
     */
    public function findTickets($ticketsQuantity)
    {
        $tickets = $this->tickets()->available()->take($ticketsQuantity)->get();

        if ($tickets->count() < $ticketsQuantity) {
            throw new NotEnoughTicketsException();
        }
        return $tickets;
    }


    public function addTickets($ticketsQuantity)
    {
        foreach (range(1, $ticketsQuantity) as $i) {
            $this->tickets()->create();
        }

        return $this;
    }

    public function ticketsTotal()
    {
        return $this->tickets()->count();
    }

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }

    public function ticketsSold()
    {
        return $this->tickets()->sold()->count();
    }

    public function percentSoldOut()
    {
        return number_format(($this->ticketsSold() / $this->ticketsTotal()) * 100, 1);
    }

    public function revenueInDollars()
    {
        $orders = Order::whereIn('id', $this->tickets()->pluck('order_id'))->get();

        return number_format($orders->sum('amount') / 100,2);
    }

    public function hasOrderFor($email)
    {
        return $this->orders()->whereEmail($email)->count() > 0;

    }

    public function ordersFor($email)
    {
        return $this->orders()->whereEmail($email);
    }

    public function hasPoster()
    {
        return $this->poster_image_path !== null;
    }

    public function posterUrl()
    {
        return Storage::disk('public')->url($this->poster_image_path);
    }

}
