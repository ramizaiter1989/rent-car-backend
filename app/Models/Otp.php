<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Otp extends Model
{
    protected $fillable = ['phone_number', 'code', 'expired_at'];

    protected $casts = [
        'expired_at' => 'datetime',
    ];

    public function isExpired()
    {
        return Carbon::now()->isAfter($this->expired_at);
    }

    public function scopeValid($query, $phone, $code)
    {
        return $query->where('phone_number', $phone)
            ->where('code', $code)
            ->where('expired_at', '>', Carbon::now())
            ->latest()
            ->first();
    }
}