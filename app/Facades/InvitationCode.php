<?php

namespace App\Facades;


use App\InvitationCodeGeneratorInterface;
use Illuminate\Support\Facades\Facade;

class InvitationCode extends Facade
{
    protected static function getFacadeAccessor()
    {
        return InvitationCodeGeneratorInterface::class;
    }
}