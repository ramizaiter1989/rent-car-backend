<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ad extends Model
{
    protected $fillable = [
        'user_id', 'website', 'company_type', 'image_url', 'target_url',
        'ads_text', 'amount_cost', 'start_at', 'expire_at', 'nb_views',
        'nb_clicks', 'online'
    ];

    protected $casts = [
        'start_at' => 'datetime',
        'expire_at' => 'datetime',
        'online' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeActive($query)
    {
        return $query->where('online', true)
            ->where('expire_at', '>', now());
    }
}

