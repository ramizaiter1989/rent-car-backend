<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    use HasFactory;

    protected $fillable = [
        'agent_id',
        'qualification_code',
        'make',
        'model',
        'year',
        'cylinder_number',
        'license_plate',
        'color',
        'mileage',
        'fuel_type',
        'transmission',
        'wheels_drive',
        'car_category',
        'car_add_on',
        'seats',
        'doors',
        'features',
        'daily_rate',
        'reason_of_rent',
        'holiday_rate',
        'is_deposit',
        'deposit',
        'status',
        'main_image_url',
        'front_image_url',
        'back_image_url',
        'left_image_url',
        'right_image_url',
        'live_location',
        'delivery_location',
        'return_location',
        'is_delivered',
        'delivery_fees',
        'with_driver',
        'driver_fees',
        'max_driving_mileage',
        'min_renting_days',
        'notes',
        'insurance_expiry',
        'registration_expiry',
        'views_count',
        'search_count',
        'car_accepted',
    ];

    protected $casts = [
        'features' => 'array',
        'reason_of_rent' => 'array',
        'live_location' => 'array',
        'delivery_location' => 'array',
        'return_location' => 'array',
        'is_deposit' => 'boolean',
        'is_delivered' => 'boolean',
        'with_driver' => 'boolean',
        'car_accepted' => 'boolean',
        'daily_rate' => 'decimal:2',
        'holiday_rate' => 'decimal:2',
        'deposit' => 'decimal:2',
        'delivery_fees' => 'decimal:2',
        'driver_fees' => 'decimal:2',
        'insurance_expiry' => 'datetime',
        'registration_expiry' => 'datetime',
    ];

    // Relationships
    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function activeBookings()
    {
        return $this->hasMany(Booking::class)
            ->whereIn('booking_request_status', ['approved', 'pending']);
    }

    public function checkPhotos()
    {
        return $this->hasMany(CheckPhoto::class);
    }

    public function tracking()
    {
        return $this->hasMany(CarTracking::class);
    }

    public function holidays()
    {
        return $this->hasMany(Holiday::class);
    }

    public function featuredListing()
    {
        return $this->hasOne(FeaturedCar::class);
    }

    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(User::class, 'favorites')
            ->withTimestamps();
    }

    public function deposits()
    {
        return $this->hasMany(DepositForCar::class);
    }

    public function balances()
    {
        return $this->hasMany(Balance::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class);
    }

    public function chats()
    {
        return $this->hasMany(Chat::class);
    }

    // Scopes
    public function scopeAvailable($query)
    {
        return $query->where('status', 'available')
            ->where('car_accepted', true);
    }

    public function scopeAccepted($query)
    {
        return $query->where('car_accepted', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('car_category', $category);
    }

    public function scopePriceRange($query, $min, $max)
    {
        return $query->whereBetween('daily_rate', [$min, $max]);
    }

    public function scopeFeatured($query)
    {
        return $query->whereHas('featuredListing', function ($q) {
            $q->where('expire_at', '>', now());
        });
    }

    // Helper methods
    public function isAvailable()
    {
        return $this->status === 'available' && $this->car_accepted;
    }

    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementSearches()
    {
        $this->increment('search_count');
    }

    public function getAverageRating()
    {
        return $this->feedbacks()->avg('rating');
    }

    public function getTotalRatings()
    {
        return $this->feedbacks()->count();
    }

    public function isBookedBetween($startDate, $endDate)
    {
        return $this->bookings()
            ->where('booking_request_status', 'approved')
            ->where(function ($query) use ($startDate, $endDate) {
                $query->whereBetween('start_datetime', [$startDate, $endDate])
                    ->orWhereBetween('end_datetime', [$startDate, $endDate])
                    ->orWhere(function ($q) use ($startDate, $endDate) {
                        $q->where('start_datetime', '<=', $startDate)
                          ->where('end_datetime', '>=', $endDate);
                    });
            })
            ->exists();
    }

    public function getTotalPrice($days, $isHoliday = false)
    {
        $rate = $isHoliday && $this->holiday_rate ? $this->holiday_rate : $this->daily_rate;
        $total = $rate * $days;
        
        if ($this->is_delivered && $this->delivery_fees) {
            $total += $this->delivery_fees;
        }
        
        if ($this->with_driver && $this->driver_fees) {
            $total += $this->driver_fees * $days;
        }
        
        return $total;
    }

    public function getDurationInDays()
    {
        return 1; // This will be calculated from booking dates
    }
}