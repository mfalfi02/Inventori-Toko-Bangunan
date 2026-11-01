<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryTransaction extends Model
{
    protected $fillable = ['number', 'type', 'status', 'created_by', 'processed_by', 'validated_by', 'transaction_date', 'notes', 'processed_at', 'validated_at', 'cancelled_at'];

    protected function casts(): array
    {
        return ['transaction_date' => 'datetime', 'processed_at' => 'datetime', 'validated_at' => 'datetime', 'cancelled_at' => 'datetime'];
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function processor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(InventoryTransactionItem::class);
    }

    #[Scope]
    protected function outgoing(Builder $query): void
    {
        $query->where('type', 'out');
    }
}
