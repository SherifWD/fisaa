<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PaymentNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public array $paymentRequest;

    public function __construct(array $paymentRequest)
    {
        $this->paymentRequest = $paymentRequest;
    }

    public function broadcastOn()
    {
        return new Channel('payment-request');
    }

    public function broadcastAs()
    {
        return 'new-payment-request';
    }
}