<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockBatch extends Model
{
    protected $fillable = ['product_id', 'receipt_number', 'quantity_received', 'quantity_remaining', 'unit_cost', 'received_at'];

    protected function casts(): array
    {
        return ['received_at' => 'date', 'unit_cost' => 'decimal:2'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
