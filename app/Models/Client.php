<?php

// ==========================================
// app/Models/Client.php
// ==========================================
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    protected $fillable = [
        'user_id', 'license_number', 'driver_license', 'profession',
        'avg_salary', 'promo_code', 'rating', 'deposit', 'bonus',
        'trusted_by_app', 'qualification_code'
    ];

    protected $casts = [
        'rating' => 'array',
        'trusted_by_app' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function qualifications()
    {
        return $this->hasMany(Qualification::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'client_id', 'user_id');
    }
}