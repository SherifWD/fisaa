<?php

use App\Events\PaymentNotification;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
Route::get('/', function () {
    return view('welcome');
});

Route::get('/event', function () {
    $array = ['name' => 'Ekpono Ambrose']; //data we want to pass
    event(new PaymentNotification($array));

    return 'done';
});
Route::post('/pusher/webhook', function (Request $request) {
    Log::info('Pusher Webhook Received:', $request->all());
    return response()->json(['status' => 'Webhook received']);
});