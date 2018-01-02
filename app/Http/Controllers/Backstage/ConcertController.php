<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConcertController extends Controller
{
    public function index()
    {
        $concerts = Auth::user()->concerts;

        return view('backstage.concerts.index', ['concerts' => $concerts]);
    }
    
    public function create()
    {
        return view('backstage.concerts.create');
    }

    public function store()
    {
        $this->validate(request(), [
            'title' => 'required',
            'date' => 'required|date',
            'time' => 'required|date_format:g:ia',
            'venue' => 'required',
            'venue_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'ticket_price' => 'required|numeric|min:5',
            'ticket_quantity' => 'required|integer|min:1'
        ]);

        $concert = Auth::user()->concerts()->create([
            'title' => request('title'),
            'subtitle' => request('subtitle'),
            'date' => Carbon::parse(vsprintf('%s %s', [
                request('date'),
                request('time'),
            ])),
            'ticket_price' =>  request('ticket_price') * 100,
            'ticket_quantity' => (int) request('ticket_quantity'),
            'venue' => request('venue'),
            'venue_address' => request('venue_address'),
            'city' => request('city'),
            'state' => request('state'),
            'zip' => request('zip'),
            'additional_information' => request('additional_information')
        ]);

        $concert->publish();

        return redirect()->route('concerts.show', ['id' => $concert->id]);
    }

    public function edit($id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        if($concert->isPublished()) {
            return response('You can not edit already published concert',403);
        }

        return view('backstage.concerts.edit', ['concert' => $concert]);
    }

    public function update($id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        if($concert->isPublished()) {
            return response('You can not update data on already published concert',403);
        }

        $this->validate(request(), [
            'title' => 'required',
            'date' => 'required|date',
            'time' => 'required|date_format:g:ia',
            'venue' => 'required',
            'venue_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'ticket_price' => 'required|numeric|min:5',
            'ticket_quantity' => 'required|integer|min:1'
        ]);

        $concert->update([
            'title' => request('title'),
            'subtitle' => request('subtitle'),
            'date' => Carbon::parse(vsprintf('%s %s', [
                request('date'),
                request('time'),
            ])),
            'ticket_price' =>  request('ticket_price') * 100,
            'ticket_quantity' => (int) request('ticket_quantity'),
            'venue' => request('venue'),
            'venue_address' => request('venue_address'),
            'city' => request('city'),
            'state' => request('state'),
            'zip' => request('zip'),
            'additional_information' => request('additional_information'),
        ]);

        return redirect()->route('backstage.concerts.index');
    }
}
