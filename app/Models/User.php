<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'username',
        'email',
        'password',
        'verified_by_admin',
        'otp_verification',
        'first_name',
        'last_name',
        'phone_number',
        'gender',
        'birth_date',
        'id_card_front',
        'id_card_back',
        'city',
        'bio',
        'role',
        'profile_picture',
        'status',
        'is_locked',
        'referred_link',
        'referral_id',
        'community_ids',
        'update_access',
        'qualification_code',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'verified_by_admin' => 'boolean',
        'otp_verification' => 'boolean',
        'birth_date' => 'date',
        'status' => 'boolean',
        'is_locked' => 'boolean',
        'update_access' => 'boolean',
        'community_ids' => 'array',
    ];

    // Self-referencing relationship for referrals
    public function referrer()
    {
        return $this->belongsTo(User::class, 'referral_id');
    }

    public function referrals()
    {
        return $this->hasMany(User::class, 'referral_id');
    }

    // User identity data
    public function realUserData()
    {
        return $this->hasOne(RealUserData::class);
    }

    // Client relationship
    public function client()
    {
        return $this->hasOne(Client::class);
    }

    // Agent relationship
    public function agent()
    {
        return $this->hasOne(Agent::class);
    }

    // Cars owned by this user (if agent)
    public function cars()
    {
        return $this->hasMany(Car::class, 'agent_id');
    }

    // Bookings made by this user (if client)
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'client_id');
    }

    // Favorites
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function favoriteCars()
    {
        return $this->belongsToMany(Car::class, 'favorites')
            ->withTimestamps();
    }

    // Financial relationships
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function balances()
    {
        return $this->hasMany(Balance::class, 'agent_id');
    }

    public function penalties()
    {
        return $this->hasMany(Penalty::class);
    }

    public function deposits()
    {
        return $this->hasMany(DepositForCar::class, 'client_id');
    }

    // Communication
    public function sentChats()
    {
        return $this->hasMany(Chat::class, 'sender_id');
    }

    public function receivedChats()
    {
        return $this->hasMany(Chat::class, 'receiver_id');
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }

    public function suggestions()
    {
        return $this->hasMany(UserSuggestion::class);
    }

    public function feedbacks()
    {
        return $this->hasMany(Feedback::class, 'client_id');
    }

    // Tracking
    public function carTrackings()
    {
        return $this->hasMany(CarTracking::class, 'client_id');
    }

    public function checkPhotos()
    {
        return $this->hasMany(CheckPhoto::class);
    }

    // Ads (for companies/advertisers)
    public function ads()
    {
        return $this->hasMany(Ad::class);
    }

    // Permissions
    public function permissions()
    {
        return $this->hasOne(UserPermission::class);
    }

    // Appeals
    public function appeals()
    {
        return $this->hasMany(Appeal::class);
    }

    // Frequent searches
    public function frequentSearches()
    {
        return $this->hasMany(FrequentSearch::class, 'client_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', true)->where('is_locked', false);
    }

    public function scopeVerified($query)
    {
        return $query->where('verified_by_admin', true);
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    // Helper methods
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isClient()
    {
        return $this->role === 'client';
    }

    public function isAgent()
    {
        return $this->role === 'agency';
    }

    public function isVerified()
    {
        return $this->verified_by_admin && $this->otp_verification;
    }
}