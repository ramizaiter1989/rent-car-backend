<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DepositForCar extends Model
{
    protected $fillable = ['car_id', 'client_id', 'amount', 'description'];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }
}
