<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'front_side_image',
        'back_side_image',
        'left_side_image',
        'right_side_image',
        'plate_number',
        'license_image',
        'car_type',
        'is_verified',
        'verification_status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getFrontSideImageAttribute($val)
    {
        return ($val !== null) ? asset('documents/' . $val) : "";
    }
    public function getBackSideImageAttribute($val)
    {
        return ($val !== null) ? asset('documents/' . $val) : "";
    }
    public function getLeftSideImageAttribute($val)
    {
        return ($val !== null) ? asset('documents/' . $val) : "";
    }
    public function getRightSideImageAttribute($val)
    {
        return ($val !== null) ? asset('documents/' . $val) : "";
    }
    public function getLicenseImageAttribute($val)
    {
        return ($val !== null) ? asset('documents/' . $val) : "";
    }
}