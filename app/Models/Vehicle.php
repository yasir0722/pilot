<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    protected $fillable = [
        'name',
        'type',
        'make',
        'model',
        'year',
        'plate_number',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
        ];
    }

    public function services(): HasMany
    {
        return $this->hasMany(VehicleService::class)->orderByDesc('date');
    }

    public function warrantyClaims(): HasMany
    {
        return $this->hasMany(VehicleWarrantyClaim::class)->orderByDesc('date_filed');
    }
}
