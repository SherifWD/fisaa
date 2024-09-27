<?php

use App\Http\Controllers\AuthController;

use App\Http\Controllers\UserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\TripController;
use App\Http\Controllers\TripTypeController;
use App\Http\Controllers\DriverCarController;
use App\Http\Middleware\JwtMiddleware;

Route::post('register', [AuthController::class, 'register']);
Route::post('login', [AuthController::class, 'login']);


Route::middleware([JwtMiddleware::class])->group(function () {

  Route::get('user', [AuthController::class, 'getAuthenticatedUser']);
  Route::post('logout', [AuthController::class, 'logout']);

  Route::post('documents/submit', [DocumentController::class, 'submitDocuments']);
  Route::put('documents/{id}/approve', [DocumentController::class, 'approveDocument']);

  Route::get('categories', [CategoryController::class, 'getCategories']);
  Route::post('categories/create', [CategoryController::class, 'createCategory']);

  Route::post('trips/create', [TripController::class, 'createTrip']);
  Route::put('trips/{trip_id}/accept/{driver_id}', [TripController::class, 'acceptTrip']);
  Route::get('users/{user_id}/trips', [TripController::class, 'getTripHistory']);

  Route::get('trip-types', [TripTypeController::class, 'getTripTypes']);
  Route::post('trip-types/create', [TripTypeController::class, 'createTripType']);

  Route::post('driver-cars/add', [DriverCarController::class, 'addCar']);
  Route::get('/driver/nearby-trips', [TripController::class, 'getNearbyTrips']);

});