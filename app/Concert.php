<?php

namespace App;

use App\Exceptions\NotEnoughTicketsException;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

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
        return $this->belongsToMany(Order::class,'tickets');
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

    public function ticketsRemaining()
    {
        return $this->tickets()->available()->count();
    }

    public function hasOrderFor($email)
    {
        return $this->orders()->whereEmail($email)->count() > 0;

    }

    public function ordersFor($email)
    {
        return $this->orders()->whereEmail($email);
    }
}
