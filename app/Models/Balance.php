<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Balance extends Model
{
    protected $fillable = [
        'agent_id', 'car_id', 'amount', 'issue_date', 'commission',
        'app_fees', 'net_profit', 'promo_code'
    ];

    protected $casts = [
        'issue_date' => 'date',
        'commission' => 'decimal:2',
        'app_fees' => 'decimal:2',
    ];

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }
}
