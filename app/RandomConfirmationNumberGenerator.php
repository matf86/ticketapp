<?php

namespace App;


class RandomConfirmationNumberGenerator implements OrderConfirmationNumberGeneratorInterface
{
    public function generate()
    {
        $pool = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        return substr(str_shuffle(str_repeat($pool, 24)), 0, 24);
    }

}