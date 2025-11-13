<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    protected $fillable = [
        'user_id', 'name', 'amount', 'reference_id', 'due_date',
        'issue_date', 'source', 'type', 'description'
    ];

    protected $casts = [
        'due_date' => 'datetime',
        'issue_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
