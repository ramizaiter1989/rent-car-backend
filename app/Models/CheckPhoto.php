<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckPhoto extends Model
{
    protected $fillable = [
        'user_id', 'booking_id', 'photo_front', 'photo_back',
        'photo_side_left', 'photo_side_right', 'photo_tableau',
        'mileage_number', 'fuel_load', 'photo_inside_front',
        'photo_inside_back'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

// ====================================
// app/Models/CarTracking.php
// ====================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarTracking extends Model
{
    protected $fillable = [
        'car_id', 'client_id', 'longitude', 'latitude',
        'red_zones', 'is_redzone'
    ];

    protected $casts = [
        'longitude' => 'array',
        'latitude' => 'array',
        'red_zones' => 'array',
        'is_redzone' => 'boolean',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
