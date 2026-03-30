<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GroceryItem extends Model
{
    protected $fillable = ['grocery_list_id', 'name', 'completed', 'sort_order'];

    protected function casts(): array
    {
        return [
            'completed' => 'boolean',
        ];
    }

    public function groceryList(): BelongsTo
    {
        return $this->belongsTo(GroceryList::class);
    }
}
