<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryTransactionItem extends Model
{
    protected $fillable = ['product_id', 'requested_quantity', 'actual_quantity', 'discrepancy_notes'];

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(InventoryTransaction::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }
}
