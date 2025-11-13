<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $fillable = ['client_id', 'car_id', 'comments', 'rating'];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}

