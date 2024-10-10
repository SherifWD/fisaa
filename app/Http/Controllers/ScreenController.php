<?php

namespace App\Http\Controllers;

use App\Http\Resources\HomeResource;
use App\Models\Category;
use App\Models\Trip;
use App\Traits\backendTraits;
use App\Traits\HelpersTrait;
use Illuminate\Http\Request;

class ScreenController extends Controller
{
    use HelpersTrait;
    use backendTraits;
    public function homeScr()
    {
        $data['trips'] = Trip::where('passenger_id', auth()->user()->id)->where('status', 'searching')->get();
        $data['categories'] = Category::all();

        $resource = HomeResource::make($data);
        return $this->returnData('data', $resource, 'Home Data');
    }
}