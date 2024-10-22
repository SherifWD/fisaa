<?php

namespace App\Http\Controllers;

use App\Events\TripAccepted;
use App\Models\Category;
use App\Models\ObjectWeight;
use App\Models\StuffType;
use App\Models\Trip;
use App\Models\TripType;
use App\Models\User;
use App\Models\Worker;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class TripController extends Controller
{
    use HelpersTrait;
    public function cancelTrip(){
        $trips = Trip::where('status','searching')->where('created_at','>',now())->get();
        foreach($trips as $trip){
            $trip->status = 'cancel';
            $trip->save();
        }
        $this->returnDate('data','$trips','Cancelled Trips');
    }
    public function createTrip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'type_id' => 'required|exists:trip_types,id',
            'from' => 'required|string',
            'from_lat' => 'required|numeric',
            'from_lng' => 'required|numeric',
            'to' => 'required|string',
            'to_lat' => 'required|numeric',
            'to_lng' => 'required|numeric',
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
        $price = $this->calculateDistancePrice(
            $request->from_lat,
            $request->from_lng,
            $request->to_lat,
            $request->to_lng
        );

        $trip['trip'] = Trip::create([
            'passenger_id' => auth()->user()->id,
            'type_id' => $request->type_id,
            'from' => $request->from,
            'from_lat' => $request->from_lat,
            'from_lng' => $request->from_lng,
            'to' => $request->to,
            'to_lat' => $request->to_lat,
            'to_lng' => $request->to_lng,
            'price' => $price,
            'is_cash' => $request->is_cash,
        ]);

        $trip['drivers'] = $this->findNearbyDrivers($request->from_lat, $request->from_lng, 'pullup');

        return $this->returnData('trip', $trip, 'Pullup trip created successfully');
    }

    private function createCarrierTrip(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'object_type' => 'required|exists:stuff_types,id',
            'weight' => 'required|exists:object_weights,id',
            'sender_name' => 'required|string|max:255',
            'sender_phone' => 'required|string|max:15',
            'receiver_name' => 'required|string|max:255',
            'receiver_phone' => 'required|string|max:15',
            'workers_needed' => 'required|exists:workers,id',
            'payment_by' => 'required|string|in:sender,receiver',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E004', $validator);
        }

        // Calculate price based on distance and additional parameters
        $distancePrice = $this->calculateDistancePrice(
            $request->from_lat,
            $request->from_lng,
            $request->to_lat,
            $request->to_lng
        );

        $workerPrice = Worker::find($request->workers_needed)->price;
        $weightPrice = ObjectWeight::find($request->weight)->price;
        $stuffTypePrice = StuffType::find($request->object_type)->price;

        $totalPrice = $distancePrice + $workerPrice + $weightPrice + $stuffTypePrice;

        $trip['trip'] = Trip::create([
            'passenger_id' => auth()->user()->id,
            'type_id' => $request->type_id,
            'from' => $request->from,
            'from_lat' => $request->from_lat,
            'from_lng' => $request->from_lng,
            'to' => $request->to,
            'to_lat' => $request->to_lat,
            'to_lng' => $request->to_lng,
            'price' => $totalPrice,
            'is_cash' => $request->is_cash,
            'stuff_type_id' => $request->object_type,
            'weight_id' => $request->weight,
            'worker_id' => $request->workers_needed,
            'sender_name' => $request->sender_name,
            'sender_phone' => $request->sender_phone,
            'receiver_name' => $request->receiver_name,
            'receiver_phone' => $request->receiver_phone,
            'payment_by' => $request->payment_by,
        ]);

        $trip['drivers'] = $this->findNearbyDrivers(
            $request->from_lat,
            $request->from_lng,
            TripType::where('name', 'carrier')->first()->id
        );

        return $this->returnData('trip', $trip, 'Carrier trip created successfully');
    }
public function getTripPrice(Request $request)
{
    $validator = Validator::make($request->all(), [
        'type_id' => 'required|exists:trip_types,id',
        'from_lat' => 'required|numeric',
        'from_lng' => 'required|numeric',
        'to_lat' => 'required|numeric',
        'to_lng' => 'required|numeric',
        'object_type' => 'nullable|exists:stuff_types,id',
        'weight' => 'nullable|exists:object_weights,id',
        'workers_needed' => 'nullable|exists:workers,id',
    ]);

    if ($validator->fails()) {
        return $this->returnValidationError('E003', $validator);
    }

    // Calculate distance-based price
    $distancePrice = $this->calculateDistancePrice(
        $request->from_lat,
        $request->from_lng,
        $request->to_lat,
        $request->to_lng
    );

    $totalPrice = $distancePrice;

    // Add additional prices for 'carrier' type trips
    $carType = TripType::find($request->type_id)->name;
    if ($carType === 'carrier') {
        $workerPrice = Worker::find($request->workers_needed)->price ?? 0;
        $weightPrice = ObjectWeight::find($request->weight)->price ?? 0;
        $stuffTypePrice = StuffType::find($request->object_type)->price ?? 0;

        $totalPrice += $workerPrice + $weightPrice + $stuffTypePrice;
    }

    return response()->json([
        'total_price' => $totalPrice,
        'message' => 'Trip price calculated successfully',
    ]);
}

    private function calculateDistancePrice($fromLat, $fromLng, $toLat, $toLng)
    {
        $distance = $this->calculateDistance($fromLat, $fromLng, $toLat, $toLng);
        $pricePerKm = env('PRICE_PER_KM', 5); // Price per km from environment config
        
        return round($distance * $pricePerKm,2);
    }

    private function calculateDistance($fromLat, $fromLng, $toLat, $toLng)
    {
        $earthRadius = 6371; // Earth's radius in kilometers

        $latFrom = deg2rad($fromLat);
        $lngFrom = deg2rad($fromLng);
        $latTo = deg2rad($toLat);
        $lngTo = deg2rad($toLng);

        $latDelta = $latTo - $latFrom;
        $lngDelta = $lngTo - $lngFrom;

        $a = sin($latDelta / 2) ** 2 +
            cos($latFrom) * cos($latTo) * sin($lngDelta / 2) ** 2;

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c; // Distance in kilometers
    }


    private function findNearbyDrivers($latitude, $longitude, $carType)
    {

        return User::where('is_driver', true)
            ->where('driver_type', $carType)
            ->where('is_available', 1)
            ->where('id', '!=', auth()->user()->id)
            ->whereRaw("ST_Distance_Sphere(point(lng, lat), point(?, ?)) <= 50000", [$longitude, $latitude])
            ->get();

    }


    public function acceptTrip($trip_id)
    {
        $driver = auth()->user();

        if (!$driver->is_driver || $driver->is_available !== 1) {
            return $this->returnError('E006', 'Driver is not available.');
        }

        $trip = Trip::find($trip_id);

        if (!$trip || $trip->status !== 'searching') {
            return $this->returnError('E005', 'Trip not available for acceptance.');
        }

        $trip->driver_id = $driver->id;
        $trip->status = 'way';
        $trip->save();

        broadcast(new TripAccepted($trip))->toOthers();

        return $this->returnData('trip', $trip, 'Trip accepted successfully.');
    }
    public function completeTrip($trip_id)
    {
        $trip = Trip::find($trip_id);

        if (!$trip || $trip->status !== 'way') {
            return $this->returnError('E007', 'Trip is not in progress.');
        }

        $trip->status = 'completed';
        $trip->save();

        return $this->returnSuccessMessage('Trip completed successfully.');
    }
    public function reviewTrip(Request $request, $trip_id)
    {
        $validator = Validator::make($request->all(), [
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E008', $validator);
        }

        $trip = Trip::find($trip_id);

        if (!$trip || $trip->status !== 'completed') {
            return $this->returnError('E009', 'Trip not eligible for review.');
        }

        // Prevent multiple reviews for the same trip
        if ($trip->review) {
            return $this->returnError('E010', 'This trip has already been reviewed.');
        }

        // Store the review
        $review = $trip->review()->create([
            'user_id' => auth()->user()->id,
            'rating' => $request->rating,
            'review' => $request->review,
        ]);

        return $this->returnData('review', $review, 'Review submitted successfully.');
    }
    public function getCurrentTripDetails($trip_id)
    {
        $trip = Trip::with(['driver', 'passenger'])->find($trip_id);

        if (!$trip) {
            return $this->returnError('E011', 'Trip not found.');
        }

        return $this->returnData('trip', $trip, 'Trip details retrieved successfully.');
    }
    public function getDriverDetails($trip_id)
    {
        $trip = Trip::with('driver')->find($trip_id);

        if (!$trip || !$trip->driver) {
            return $this->returnError('E012', 'Driver not found for this trip.');
        }

        return $this->returnData('driver', $trip->driver, 'Driver details retrieved successfully.');
    }


    public function getStuffTypes()
    {
        $data['types'] = StuffType::all();
        $data['workers'] = Worker::all();
        $data['weight'] = ObjectWeight::all();
        $data['categories'] = Category::all();
        return $this->returnData('trips', $data, 'Trip history retrieved successfully');
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