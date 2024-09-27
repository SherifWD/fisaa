<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CarRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'car_image',
        'plate_num',
        'driver_image',
        'license_image',
        'driver_id'
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}