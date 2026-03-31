<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleService extends Model
{
    protected $fillable = [
        'vehicle_id',
        'description',
        'service_type',
        'mileage',
        'cost',
        'date',
        'next_service_date',
        'next_service_mileage',
        'notes',
        'receipt_path',
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'date' => 'date',
            'next_service_date' => 'date',
            'mileage' => 'integer',
            'next_service_mileage' => 'integer',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
