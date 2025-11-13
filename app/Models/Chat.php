<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Chat extends Model
{
    protected $fillable = [
        'client_id', 'agent_id', 'car_id', 'sender_id',
        'receiver_id', 'chat_text', 'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function agent()
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
