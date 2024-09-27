<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DriverCar extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_id',
        'driver_id',
        'car_model',
        'car_make',
        'year'
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}