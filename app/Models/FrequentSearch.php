<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FrequentSearch extends Model
{
    protected $fillable = [
        'client_id', 'make', 'model', 'year', 'cylinder_number',
        'color', 'mileage', 'fuel_type', 'transmission', 'wheels_drive',
        'car_category', 'car_add_on', 'seats', 'doors', 'features',
        'daily_rate', 'holiday_rate', 'is_deposit', 'deposit',
        'delivery_location', 'return_location', 'is_delivered',
        'delivery_fees', 'with_driver', 'driver_fees',
        'max_driving_mileage', 'min_renting_days'
    ];

    protected $casts = [
        'features' => 'array',
        'delivery_location' => 'array',
        'return_location' => 'array',
        'is_deposit' => 'boolean',
        'is_delivered' => 'boolean',
        'with_driver' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
