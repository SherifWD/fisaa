<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Laravel\Sanctum\HasApiTokens;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;

class User extends Authenticatable implements JWTSubject, FilamentUser
{
    use HasApiTokens, HasFactory, Notifiable;

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }
public function canAccessPanel(Panel $panel): bool
{
    return true;
}
    public function canAccessFilament(): bool
    {
        // Grant access only to specific users or all for testing
        return true;
    }

    protected $fillable = [
        'name',
        'email',
        'phone',
        'country_code',
        'is_driver',
        'driver_type',
        'email_verified_at',
        'password',
        'lat',
        'lng'
    ];

    public function documents()
    {
        return $this->hasOne(Document::class);
    }

    public function driverCars()
    {
        return $this->hasMany(DriverCar::class);
    }

    public function driverTrips()
    {
        return $this->hasMany(Trip::class, 'driver_id');
    }

    public function passengerTrips()
    {
        return $this->hasMany(Trip::class, 'passenger_id');
    }

    public function carRequests()
    {
        return $this->hasMany(CarRequest::class, 'driver_id');
    }
}
