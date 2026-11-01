<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreStockInRequest;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class StockInController extends Controller
{
    public function create()
    {
        return view('stock.in', ['products' => Product::active()->orderBy('name')->get(), 'movements' => StockMovement::with(['product', 'user'])->where('type', 'in')->latest('moved_at')->take(20)->get()]);
    }

    public function store(StoreStockInRequest $request)
    {
        $data = $request->validated();
        DB::transaction(function () use ($data, $request) {
            $stockBefore = StockBatch::where('product_id', $data['product_id'])->sum('quantity_remaining');
            $batch = StockBatch::create(['product_id' => $data['product_id'], 'receipt_number' => $data['receipt_number'] ?? null, 'quantity_received' => $data['quantity'], 'quantity_remaining' => $data['quantity'], 'unit_cost' => $data['unit_cost'] ?? 0, 'received_at' => $data['moved_at']]);
            StockMovement::create(['product_id' => $data['product_id'], 'stock_batch_id' => $batch->id, 'user_id' => $request->user()->id, 'type' => 'in', 'quantity' => $data['quantity'], 'stock_before' => $stockBefore, 'stock_after' => $stockBefore + $data['quantity'], 'reference_number' => $data['receipt_number'] ?? null, 'notes' => $data['notes'] ?? null, 'moved_at' => $data['moved_at']]);
        });

        return back()->with('success', 'Barang masuk dikonfirmasi dan dicatat sebagai batch FIFO.');
    }
}
