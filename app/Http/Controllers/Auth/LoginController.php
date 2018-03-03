<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login()
    {
        if(! Auth::attempt(request(['email','password']))) {

            return response()->redirectTo('/login')->withInput(request(['email']))
                ->withErrors(['email' => "This credentials don't match our records"]);

        }

        return response()->redirectTo('/backstage/concerts');
    }

    public function logout()
    {
        Auth::logout();

        return response()->redirectTo('/login');
    }
}
