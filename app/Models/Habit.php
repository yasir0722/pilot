<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Habit extends Model
{
    protected $fillable = ['name', 'frequency', 'reminder_time', 'active'];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
            'reminder_time' => 'datetime:H:i',
        ];
    }

    public function completions(): HasMany
    {
        return $this->hasMany(HabitCompletion::class);
    }

    public function isCompletedToday(): bool
    {
        return $this->completions()->where('completed_date', today())->exists();
    }
}
