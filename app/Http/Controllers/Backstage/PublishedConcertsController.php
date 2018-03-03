<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class PublishedConcertsController extends Controller
{
    public function store()
    {
        $concert = Auth::user()->concerts()->findOrFail(request('concert_id'));

        if($concert->isPublished()) {
            return response('', 422);
        }

        $concert->publish();

        return back();
    }
}
