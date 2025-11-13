<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Qualification extends Model
{
    protected $fillable = [
        'client_id', 'gender', 'is_trusted_vip', 'deposit',
        'is_verified_by_admin', 'age', 'salary', 'location',
        'rating', 'code'
    ];

    protected $casts = [
        'is_trusted_vip' => 'boolean',
        'deposit' => 'boolean',
        'is_verified_by_admin' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
