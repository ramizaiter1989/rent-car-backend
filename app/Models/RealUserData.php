<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RealUserData extends Model
{
    protected $fillable = [
        'user_id', 'id_number', 'passport_number', 'first_name',
        'middle_name', 'last_name', 'mother_name', 'place_of_birth',
        'date_of_birth', 'status', 'reason_of_status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
