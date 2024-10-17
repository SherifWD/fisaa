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

// Step 1: Request phone number and send OTP
Route::post('request-phone-number', [AuthController::class, 'requestPhoneNumber']);

// Step 2: Validate OTP
Route::post('validate-otp', [AuthController::class, 'validateOtp']);

// Step 3: Complete registration
Route::post('register', [AuthController::class, 'register']);

// Login route
Route::post('login', [AuthController::class, 'login']);
Route::post('login-validate-login', [AuthController::class, 'loginValidateOtp']);

Route::middleware([JwtMiddleware::class])->group(function () {

  Route::get('user', [AuthController::class, 'getAuthenticatedUser']);
  Route::post('logout', [AuthController::class, 'logout']);

  Route::post('documents/submit', [DocumentController::class, 'submitDocuments']);
  Route::put('documents/{id}/approve', [DocumentController::class, 'approveDocument']);

  Route::get('categories', [CategoryController::class, 'getCategories']);
  Route::post('categories/create', [CategoryController::class, 'createCategory']);

  Route::post('trips/create', [TripController::class, 'createTrip']);
  Route::put('trips/{trip_id}/accept/{driver_id}', [TripController::class, 'acceptTrip']);
  Route::put('trips/{trip_id}/complete/{stat}', [DriverController::class, 'completeTrip']);
  Route::put('trips/{trip_id}/cancel', [TripController::class, 'cancelTrip']);
  Route::get('users/{user_id}/trips', [TripController::class, 'getTripHistory']);

  Route::get('get-stuff-types', [TripController::class, 'getStuffTypes']);
  Route::get('trip-types', [TripTypeController::class, 'getTripTypes']);
  Route::post('trip-types/create', [TripTypeController::class, 'createTripType']);

  Route::post('driver-cars/add', [DriverCarController::class, 'addCar']);
  Route::get('driver/nearby-trips', [TripController::class, 'getNearbyTrips']);
  Route::post('driver/update-location', [DriverController::class, 'updateLocation']);

  Route::get('/homeScr', [ScreenController::class, 'homeScr']);

});
Route::get('/test-broadcast', function () {
  broadcast(new \App\Events\TestEvent());
  return 'Broadcasted';
});