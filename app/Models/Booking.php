<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'client_id',
        'car_id',
        'start_datetime',
        'end_datetime',
        'total_booking_price',
        'cancelation_date',
        'expanding_date',
        'extra_charge',
        'booking_request_status',
        'cancelation_reason',
        'reason_of_booking',
    ];

    protected $casts = [
        'start_datetime' => 'datetime',
        'end_datetime' => 'datetime',
        'cancelation_date' => 'datetime',
        'expanding_date' => 'datetime',
        'total_booking_price' => 'decimal:2',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function checkPhotos()
    {
        return $this->hasMany(CheckPhoto::class);
    }

    public function appeals()
    {
        return $this->hasMany(Appeal::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('booking_request_status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('booking_request_status', 'approved');
    }

    // Helpers
    public function getDurationInDays()
    {
        return $this->start_datetime->diffInDays($this->end_datetime);
    }

    public function isActive()
    {
        return $this->booking_request_status === 'approved' 
            && now()->between($this->start_datetime, $this->end_datetime);
    }
}