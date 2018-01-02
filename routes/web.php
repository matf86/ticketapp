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

Route::get('/', function() {
   return 'Example';
});

Route::get('/concerts/{id}', 'ConcertsController@show')->name('concerts.show');

Route::post('/concerts/{id}/orders', 'ConcertOrdersController@store');

Route::get('/orders/{confirmationNumber}', 'OrdersController@show');


Route::get('/login', 'Auth\LoginController@showLoginForm')->name('auth.show-login');

Route::post('/login', 'Auth\LoginController@login')->name('login');

Route::post('/logout', 'Auth\LoginController@logout')->name('logout');


Route::group(['middleware' => 'auth', 'prefix' => 'backstage', 'namespace' => 'Backstage'], function() {

    Route::get('/concerts', 'ConcertController@index')->name('backstage.concerts.index');

    Route::get('/concerts/{concert}/edit', 'ConcertController@edit')->name('backstage.concerts.edit');

    Route::get('/concerts/new', 'ConcertController@create')->name('backstage.concerts.new');

    Route::post('/concerts', 'ConcertController@store');

    Route::patch("/concerts/{concert}", 'ConcertController@update')->name('backstage.concerts.update');
});

