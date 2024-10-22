<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Models\Trip;

class CancelTripCommand extends Command
{
    protected $signature = 'trips:cancel';
    protected $description = 'Cancel trips that are still searching';

    public function handle()
    {
        $trips = Trip::where('status', 'searching')
            ->where('created_at', '<', Carbon::now()->subHour())
            ->get();

        foreach ($trips as $trip) {
            $trip->status = 'cancel';
            $trip->save();
        }

        $this->info('Cancelled Trips: ' . $trips->count());
    }
}