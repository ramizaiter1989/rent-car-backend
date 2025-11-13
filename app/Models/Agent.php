<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Agent extends Model
{
    protected $fillable = [
        'user_id', 'business_type', 'business_doc', 'company_number',
        'location', 'app_fees', 'profession', 'contract_form',
        'policies', 'website'
    ];

    protected $casts = [
        'business_doc' => 'array',
        'location' => 'array',
        'app_fees' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function cars()
    {
        return $this->hasMany(Car::class, 'agent_id', 'user_id');
    }
}
