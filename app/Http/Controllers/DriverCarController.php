<?php

namespace App\Http\Controllers;

use App\Models\DriverCar;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class DriverCarController extends Controller
{
    use HelpersTrait;

    public function addCar(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'driver_id' => 'required|exists:users,id',
            'car_model' => 'required|string',
            'car_make' => 'required|string',
            'year' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        $car = DriverCar::create($request->all());

        return $this->returnData('car', $car, 'Car added successfully');
    }
}