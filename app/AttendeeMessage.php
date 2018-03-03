<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class AttendeeMessage extends Model
{
    protected $guarded = [];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }


    public function withRecipients($callback)
    {
        $this->concert->orders()->chunk(20, function($orders) use ($callback) {
            $callback($orders->pluck('email'));
        });
    }
}
