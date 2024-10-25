<?php

use App\Events\PaymentNotification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use Livewire\Livewire;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/event', function () {
    $array = ['name' => 'Ekpono Ambrose']; //data we want to pass
    event(new PaymentNotification($array));

    return 'done';
});
Livewire::setUpdateRoute(function ($handle) {
    $url = 'fisaa/public/livewire/update';
    return Route::post($url, $handle);
});