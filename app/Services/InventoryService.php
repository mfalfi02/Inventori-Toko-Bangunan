<?php

namespace App\Services;

use App\Models\InventoryTransaction;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function confirmOutgoing(InventoryTransaction $transaction, User $user, int $actualQuantity, ?string $discrepancyNotes = null): InventoryTransaction
    {
        return DB::transaction(function () use ($transaction, $user, $actualQuantity, $discrepancyNotes): InventoryTransaction {
            $transaction = InventoryTransaction::query()->lockForUpdate()->with('items.product')->findOrFail($transaction->id);
            if ($transaction->type !== 'out' || $transaction->status !== 'pending' || $transaction->items->count() !== 1) {
                throw ValidationException::withMessages(['transaction' => 'Nota ini tidak dapat diproses pada status saat ini.']);
            }

            $item = $transaction->items->first();
            $batches = StockBatch::query()->where('product_id', $item->product_id)->where('quantity_remaining', '>', 0)->orderBy('received_at')->orderBy('id')->lockForUpdate()->get();
            $stockBefore = $batches->sum('quantity_remaining');
            if ($actualQuantity < 1 || $stockBefore < $actualQuantity) {
                throw ValidationException::withMessages(['actual_quantity' => $actualQuantity < 1 ? 'Jumlah aktual minimal 1.' : 'Stok tidak mencukupi untuk pengeluaran ini.']);
            }

            $remaining = $actualQuantity;
            foreach ($batches as $batch) {
                if ($remaining === 0) {
                    break;
                }
                $taken = min($remaining, $batch->quantity_remaining);
                $batch->decrement('quantity_remaining', $taken);
                StockMovement::create(['product_id' => $item->product_id, 'stock_batch_id' => $batch->id, 'inventory_transaction_item_id' => $item->id, 'user_id' => $user->id, 'type' => 'out', 'quantity' => $taken, 'stock_before' => $stockBefore, 'stock_after' => $stockBefore - $taken, 'reference_number' => $transaction->number, 'notes' => $discrepancyNotes, 'moved_at' => now()]);
                $stockBefore -= $taken;
                $remaining -= $taken;
            }
            $item->update(['actual_quantity' => $actualQuantity, 'discrepancy_notes' => $discrepancyNotes]);
            $transaction->update(['status' => $actualQuantity === $item->requested_quantity ? 'submitted_for_review' : 'discrepancy', 'processed_by' => $user->id, 'processed_at' => now()]);

            return $transaction->fresh('items.product');
        }, 3);
    }
}
