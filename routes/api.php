<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ScreenController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripTypeController;
use App\Http\Controllers\DriverCarController;
use App\Http\Controllers\DriverController;
use App\Http\Middleware\JwtMiddleware;
use Illuminate\Http\Request;
Route::post('/pusher/webhook', function (Request $request) {
    Log::info('Pusher Webhook Received:', $request->all());
    return response()->json(['status' => 'Webhook received']);
});
// Authentication Routes
Route::prefix('auth')->group(function () {
  Route::post('request-phone-number', [AuthController::class, 'requestPhoneNumber']);
  Route::post('validate-otp', [AuthController::class, 'validateOtp']);
  Route::post('register', [AuthController::class, 'register']);
  Route::post('login', [AuthController::class, 'login']);
  Route::post('login-validate-otp', [AuthController::class, 'loginValidateOtp']);
});

    Route::get('get-stuff-types', [TripController::class, 'getStuffTypes']);
// Routes protected by JWT middleware
Route::middleware([JwtMiddleware::class])->group(function () {
    Route::prefix('auth')->group(function () {
  Route::post('update-profile', [AuthController::class, 'updateProfile']);
});
  // User Routes
  Route::get('user', [AuthController::class, 'getAuthenticatedUser']);
  Route::post('logout', [AuthController::class, 'logout']);

  // Document Routes
  Route::prefix('documents')->group(function () {
    Route::post('submit', [DocumentController::class, 'submitDocuments']);
    Route::put('{id}/approve', [DocumentController::class, 'approveDocument']);
  });

  // Category Routes
  Route::prefix('categories')->group(function () {
    Route::get('/', [CategoryController::class, 'getCategories']);
    Route::post('create', [CategoryController::class, 'createCategory']);
  });

  // Trip Routes
  Route::prefix('trips')->group(function () {
    Route::post('create', [TripController::class, 'createTrip']);
    Route::post('/trip/price', [TripController::class, 'getTripPrice']);

    Route::put('{trip_id}/cancel', [TripController::class, 'cancelTrip']);
    Route::get('{trip_id}/details', [TripController::class, 'getCurrentTripDetails']);
    Route::get('{trip_id}/driver', [TripController::class, 'getDriverDetails']);
    Route::post('{trip_id}/accept', [TripController::class, 'acceptTrip']);
    Route::post('{trip_id}/complete', [TripController::class, 'completeTrip']);
    Route::post('{trip_id}/review', [TripController::class, 'reviewTrip']);
    Route::get('users/{user_id}/history', [TripController::class, 'getTripHistory']);
    Route::get('driver/nearby', [TripController::class, 'getNearbyTrips']);
  });

  // Trip Type Routes
  Route::prefix('trip-types')->group(function () {
    Route::get('/', [TripTypeController::class, 'getTripTypes']);
    Route::post('create', [TripTypeController::class, 'createTripType']);
  });

  // Driver Car Routes
  Route::prefix('driver-cars')->group(function () {
    Route::post('add', [DriverCarController::class, 'addCar']);
  });

  // Driver Routes
  Route::prefix('driver')->group(function () {
    Route::any('update-location', [DriverController::class, 'updateLocation']);
  });

  // Screen Routes
  Route::get('homeScr', [ScreenController::class, 'homeScr']);
});

// Test Broadcast Route
Route::get('/test-broadcast', function () {
  broadcast(new \App\Events\TestEvent());
  return 'Broadcasted';
});