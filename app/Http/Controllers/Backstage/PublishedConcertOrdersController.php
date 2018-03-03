<?php

namespace App\Http\Controllers\Backstage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PublishedConcertOrdersController extends Controller
{
    public function index($concertId)
    {
        $concert = Auth::user()->concerts()->published()->findOrFail($concertId);

        $orders = $concert->orders()->latest()->take(10)->get();

        return view('backstage.published-concert-orders.index', [
            'concert' => $concert,
            'orders' => $orders
        ]);
    }
}
