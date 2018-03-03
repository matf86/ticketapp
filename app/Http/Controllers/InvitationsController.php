<?php

namespace App\Http\Controllers;

use App\Invitation;
use Illuminate\Http\Request;

class InvitationsController extends Controller
{
    public function show($code)
    {
        $invitation = Invitation::findByCode($code);

        if($invitation->hasBeenUsed()) {

            return response('Specified invitation is inactive')->setStatusCode(404);

        }

        return view('invitations.show', ['invitation' => $invitation]);
    }
}
