<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Penalty extends Model
{
    protected $fillable = ['user_id', 'reason', 'amount'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
