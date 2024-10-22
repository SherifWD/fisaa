<?php

namespace App\Http\Controllers;

use App\Events\DriverLocationUpdated;
use App\Events\TripAccepted;
use App\Models\Trip;
use App\Models\User;
use Illuminate\Http\Request;
use App\Traits\HelpersTrait;
use Illuminate\Support\Facades\Validator;

class DriverController extends Controller
{
    use HelpersTrait;

    public function updateLocation(Request $request)
    {
        $driver = auth()->user();

        // Ensure the authenticated user is valid and is a driver
        if (!$driver || !$driver->is_driver) {
            return $this->returnError('E002', 'User is not a driver.');
        }

        // Validate the latitude and longitude
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        // Update the driver's location
        $driver->lat = $request->lat;
        $driver->lng = $request->lng;
        $driver->save();

        // Broadcast the driver's updated location
        broadcast(new DriverLocationUpdated($driver))->toOthers();

        return $this->returnSuccessMessage('Location updated successfully');
    }


    public function completeTrip($trip_id, $stat)
    {
        $trip = Trip::find($trip_id);

        if (!$trip || ($trip->status !== 'way' && $trip->status !== 'load')) {
            return $this->returnError('E003', 'Trip not found or cannot be changed.');
        }

        $trip->status = $stat;
        $trip->save();

        return $this->returnSuccessMessage('Trip completed successfully');
    }

    public function acceptTrip($trip_id)
    {
        $trip = Trip::find($trip_id);

        if (!$trip) {
            return $this->returnError('E003', 'Trip not found');
        }

        if ($trip->status !== 'requested') {
            return $this->returnError('E005', 'Trip has already been accepted.');
        }

        $driver = auth()->user();

        if (!$driver->is_driver || $driver->status !== 'available') {
            return $this->returnError('E006', 'Driver is not available.');
        }

        $trip->driver_id = $driver->id;
        $trip->status = 'accepted';
        $trip->save();

        // Notify the rider about the accepted trip using events or sockets
        broadcast(new TripAccepted($trip))->toOthers();

        return $this->returnSuccessMessage('Trip accepted successfully');
    }
}