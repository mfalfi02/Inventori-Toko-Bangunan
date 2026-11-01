<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockOutRequest;
use App\Models\InventoryTransaction;
use App\Models\Product;
use App\Services\InventoryService;

class StockOutController extends Controller
{
    public function create()
    {
        return view('stock.out', ['products' => Product::active()->withSum('batches as stock', 'quantity_remaining')->orderBy('name')->get(), 'transactions' => InventoryTransaction::with(['items.product', 'creator', 'processor'])->outgoing()->latest()->take(20)->get()]);
    }

    public function store(StoreStockOutRequest $request)
    {
        $data = $request->validated();
        $transaction = InventoryTransaction::create(['number' => ($data['reference_number'] ?? null) ?: 'OUT-'.now()->format('YmdHis').'-'.str_pad((string) random_int(1, 999), 3, '0', STR_PAD_LEFT), 'type' => 'out', 'status' => 'pending', 'created_by' => $request->user()->id, 'transaction_date' => $data['moved_at'], 'notes' => $data['notes'] ?? null]);
        $transaction->items()->create(['product_id' => $data['product_id'], 'requested_quantity' => $data['quantity']]);

        return back()->with('success', 'Nota pengeluaran '.$transaction->number.' menunggu konfirmasi gudang.');
    }

    public function confirm(InventoryTransaction $transaction, InventoryService $inventoryService)
    {
        abort_unless(request()->user()?->role === 'staff', 403);
        request()->validate(['actual_quantity' => ['required', 'integer', 'min:1'], 'discrepancy_notes' => ['nullable', 'string', 'max:1000']]);
        $inventoryService->confirmOutgoing($transaction, request()->user(), request()->integer('actual_quantity'), request()->input('discrepancy_notes'));

        return back()->with('success', 'Pengeluaran dikonfirmasi dan stok dikurangi satu kali.');
    }

    public function validateTransaction(InventoryTransaction $transaction)
    {
        abort_unless(request()->user()?->role === 'owner', 403);
        abort_unless(in_array($transaction->status, ['submitted_for_review', 'discrepancy'], true), 422);
        $transaction->update(['status' => 'completed', 'validated_by' => request()->user()->id, 'validated_at' => now()]);

        return back()->with('success', 'Nota telah divalidasi tanpa perubahan stok tambahan.');
    }
}
