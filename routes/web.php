<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/concerts/{id}', 'ConcertsController@show')->name('concerts.show');

Route::post('/concerts/{id}/orders', 'ConcertOrdersController@store');

Route::get('/orders/{confirmationNumber}', 'OrdersController@show');


Route::get('/login', 'Auth\LoginController@showLoginForm')->name('auth.show-login');

Route::post('/login', 'Auth\LoginController@login')->name('login');

Route::post('/logout', 'Auth\LoginController@logout')->name('logout');

Route::post('/register', 'Auth\RegisterController@register')->name('register');

Route::get('/invitations/{code}', 'InvitationsController@show')->name('invitations.show');


Route::group(['middleware' => 'auth', 'prefix' => 'backstage', 'namespace' => 'Backstage'], function() {


    Route::group(['middleware' => \App\Http\Middleware\ForceStripeAccount::class],function(){

        Route::get('/concerts', 'ConcertController@index')->name('backstage.concerts.index');

        Route::get('/concerts/{concert}/edit', 'ConcertController@edit')->name('backstage.concerts.edit');

        Route::get('/concerts/new', 'ConcertController@create')->name('backstage.concerts.new');

        Route::post('/concerts', 'ConcertController@store')->name('backstage.concerts.store');

        Route::patch("/concerts/{concert}", 'ConcertController@update')->name('backstage.concerts.update');

        Route::post('/published-concerts', 'PublishedConcertsController@store')->name('backstage.published-concerts.store');

        Route::get('/published-concerts/{id}/orders', 'PublishedConcertOrdersController@index')->name('backstage.published-concert-orders.index');

        Route::get('/concerts/{id}/messages/new', 'ConcertMessagesController@create')->name('backstage.concert-messages.new');

        Route::post('/concerts/{id}/messages', 'ConcertMessagesController@store')->name('backstage.concert-messages.store');

    });

    Route::get('/stripe-connect/connect', 'StripeConnectController@connect')->name('backstage.stripe-connect.connect');

    Route::get('/stripe-connect/authorize', 'StripeConnectController@authorizeRedirect')->name('backstage.stripe-connect.authorize');;

    Route::get('/stripe-connect/redirect', 'StripeConnectController@redirect')->name('backstage.stripe-connect.redirect');;
});

