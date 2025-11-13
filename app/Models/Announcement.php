<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Announcement extends Model
{
    protected $fillable = ['user_ids', 'message', 'message_datetime', 'category'];

    protected $casts = [
        'user_ids' => 'array',
        'category' => 'array',
        'message_datetime' => 'datetime',
    ];
}
