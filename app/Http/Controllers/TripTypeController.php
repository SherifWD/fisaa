<?php

namespace App\Http\Controllers;

use App\Models\TripType;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
class TripTypeController extends Controller
{
    use HelpersTrait;

    public function getTripTypes()
    {
        $types = TripType::all();

        return $this->returnData('types', $types, 'Trip types retrieved successfully');
    }

    public function createTripType(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->returnValidationError('E001', $validator);
        }

        $type = TripType::create($request->all());

        return $this->returnData('type', $type, 'Trip type created successfully');
    }
}