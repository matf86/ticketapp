<?php

namespace App;

use App\Facades\InvitationCode;
use App\Mail\PromoterInvitationEmail;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;

class Invitation extends Model
{
    protected $guarded = [];

    public static function createFor($email)
    {
       return self::create([
            'code' => InvitationCode::generate(),
            'email' => $email
        ]);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public static function findByCode($code)
    {
        return self::whereCode($code)->firstOrFail();
    }

    public function hasBeenUsed()
    {
        return (bool) $this->user_id;
    }

    public function send()
    {
        Mail::to($this->email)->send(new PromoterInvitationEmail($this));
    }
}
