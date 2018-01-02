<?php
/**
 * Created by PhpStorm.
 * User: mateusz
 * Date: 27.12.17
 * Time: 16:14
 */

namespace App;


interface TicketCodeGeneratorInterface
{
    public function generateFor(Ticket $ticket);
}