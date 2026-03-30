<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class GroceryList extends Model
{
    protected $fillable = ['name', 'is_template'];

    protected function casts(): array
    {
        return [
            'is_template' => 'boolean',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(GroceryItem::class)->orderBy('sort_order');
    }
}
