<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver_id',
        'passenger_id',
        'type_id',
        'weight',
        'count_of_workers',
        'from',
        'from_lat',
        'from_lng',
        'to',
        'to_lat',
        'to_lng',
        'price',
        'weight_id',
        'worker_id',
        'stuff_type_id',
        'is_cash',
        'sender_name',
        'sender_phone',
        'receiver_name',
        'receiver_phone',
        'payment_by',
        'estimated_distance'
    ];

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function passenger()
    {
        return $this->belongsTo(User::class, 'passenger_id');
    }

    public function type()
    {
        return $this->belongsTo(TripType::class, 'type_id');
    }
    public function worker()
    {
        return $this->belongsTo(Worker::class, 'worker_id');
    }
    public function weight()
    {
        return $this->belongsTo(ObjectWeight::class, 'weight_id');
    }


    public function review()
    {
        return $this->hasOne(TripReview::class);
    }

}