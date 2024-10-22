<?php
namespace App\Events;

use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $driver;

    /**
     * Ensure that only a valid User instance is passed.
     */
    public function __construct(User $driver)
    {
        $this->driver = $driver;
    }

    public function broadcastOn()
    {
        return new Channel('drivers.map');
    }

    public function broadcastWith()
    {
        return [
            'driver_id' => $this->driver->id,
            'lat' => $this->driver->lat,
            'lng' => $this->driver->lng,
        ];
    }
}