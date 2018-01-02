<?php


class ConcertFactory
{
    public static function createPublished($overrides)
    {
        $concert = factory(\App\Concert::class)->create($overrides);
        $concert->publish();

        return $concert;
    }
}