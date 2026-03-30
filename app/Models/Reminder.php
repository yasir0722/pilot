<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    protected $fillable = [
        'title',
        'body',
        'remind_at',
        'recurrence',
        'sent',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'remind_at' => 'datetime',
            'sent' => 'boolean',
            'active' => 'boolean',
        ];
    }
}
