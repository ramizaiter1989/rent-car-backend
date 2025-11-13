<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedCar extends Model
{
    protected $fillable = ['car_id', 'duration', 'start_at', 'expire_at'];

    protected $casts = [
        'start_at' => 'datetime',
        'expire_at' => 'datetime',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function scopeActive($query)
    {
        return $query->where('expire_at', '>', now());
    }
}

