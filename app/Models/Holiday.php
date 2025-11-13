<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Holiday extends Model
{
    protected $fillable = ['car_id', 'holiday_name', 'holiday_dates'];

    protected $casts = [
        'holiday_dates' => 'array',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
