<?php

namespace App\Http\Controllers;

use App\Models\Trip;
use App\Models\TripType;
use App\Models\User;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class TripController extends Controller
{
    use HelpersTrait;

    public function createTrip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:trip_types,id',
            // 'car_type' => 'required|string|in:pullup,carrier',
            'from' => 'required|string',
            'from_lat' => 'required|string',
            'from_lng' => 'required|string',
            'to' => 'required|string',
            'to_lat' => 'required|string',
            'to_lng' => 'required|string',
            'price' => 'required|numeric',
            'is_cash' => 'boolean',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        $carType = TripType::find($request->type_id)->name;
        if ($carType === 'pullup') {
            return $this->createPullupTrip($request);
        } elseif ($carType === 'carrier') {
            return $this->createCarrierTrip($request);
        }

        return $this->returnError('E002', 'Invalid car type');
    }

    private function createPullupTrip(Request $request)
    {
        $trip['drivers'] = $this->findNearbyDrivers($request->from_lat, $request->from_lng, 'pullup');

        if ($trip['drivers']->isEmpty()) {
            return $this->returnError('E003', 'No drivers found nearby for pullup.');
        }

        $trip['trip'] = Trip::create([
            'passenger_id' => auth()->user()->id,
            'type_id' => $request->type_id,
            'from' => $request->from,
            'from_lat' => $request->from_lat,
            'from_lng' => $request->from_lng,
            'to' => $request->to,
            'to_lat' => $request->to_lat,
            'to_lng' => $request->to_lng,
            'price' => $request->price,
            'is_cash' => $request->is_cash,
        ]);

        return $this->returnData('trip', $trip, 'Pullup trip created successfully');
    }
    private function createCarrierTrip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'object_type' => 'required|string|in:Building materials,Furniture,Food,Electrical objects,Others',
            'weight' => 'required|string|in:100kg,200kg,300kg+',
            'sender_name' => 'required|string',
            'sender_phone' => 'required|string',
            'receiver_name' => 'required|string',
            'receiver_phone' => 'required|string',
            'workers_needed' => 'required|in:0,1,2,3+',
            'payment_by' => 'required|string|in:sender,receiver',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E004', $validator);
        }

        $trip['drivers'] = $this->findNearbyDrivers($request->from_lat, $request->from_lng, TripType::where('name', 'carrier')->first()->id);

        if ($trip['drivers']->isEmpty()) {
            return $this->returnError('E003', 'No drivers found nearby for carrier.');
        }

        $trip['trip'] = Trip::create([
            'passenger_id' => auth()->user()->id,
            'type_id' => $request->type_id,
            'from' => $request->from,
            'from_lat' => $request->from_lat,
            'from_lng' => $request->from_lng,
            'to' => $request->to,
            'to_lat' => $request->to_lat,
            'to_lng' => $request->to_lng,
            'price' => $request->price,
            'is_cash' => $request->is_cash,
            'object_type' => $request->object_type,
            'weight' => $request->weight,
            'sender_name' => $request->sender_name,
            'sender_phone' => $request->sender_phone,
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'workers_needed' => $request->workers_needed,
            'payment_by' => $request->payment_by,
        ]);

        return $this->returnData('trip', $trip, 'Carrier trip created successfully');
    }
    private function findNearbyDrivers($latitude, $longitude, $carType)
    {

        return User::where('is_driver', true)
            ->where('driver_type', $carType)->where('id', '!=', auth()->user()->id)
            ->whereRaw("ST_Distance_Sphere(point(lng, lat), point(?, ?)) <= 50000", [$longitude, $latitude])
            ->get();
    }


    public function acceptTrip($trip_id, $driver_id)
    {
        $trip = Trip::find($trip_id);

        if (!$trip) {
            return $this->returnError('E003', 'Trip not found');
        }

        $trip->driver_id = $driver_id;
        $trip->save();

        return $this->returnSuccessMessage('Trip accepted successfully');
    }

    public function getTripHistory($user_id)
    {
        $trips = Trip::where('passenger_id', $user_id)->orWhere('driver_id', $user_id)->get();

        return $this->returnData('trips', $trips, 'Trip history retrieved successfully');
    }
    public function getNearbyTrips(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'lat' => 'required|numeric',
            'lng' => 'required|numeric',
            'radius' => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        $driver = auth()->user();

        if (!$driver->is_driver) {
            return $this->returnError('E002', 'User is not a driver.');
        }

        $nearbyTrips = Trip::whereNull('driver_id')
            ->whereRaw("
                ST_Distance_Sphere(point(from_lng, from_lat), point(?, ?)) <= ?
            ", [$request->lng, $request->lat, $request->radius * 1000])
            ->get();

        return $this->returnData('trips', $nearbyTrips, 'Nearby trips retrieved successfully.');
    }

}