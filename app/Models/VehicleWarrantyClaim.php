<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VehicleWarrantyClaim extends Model
{
    protected $fillable = [
        'vehicle_id',
        'description',
        'claim_number',
        'status',
        'date_filed',
        'date_resolved',
        'cost_covered',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_filed' => 'date',
            'date_resolved' => 'date',
            'cost_covered' => 'decimal:2',
        ];
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }
}
