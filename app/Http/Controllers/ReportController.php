<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $from = $request->input('from', now()->startOfMonth()->toDateString());
        $to = $request->input('to', now()->toDateString());
        abort_if($from > $to, 422, 'Tanggal awal tidak boleh setelah tanggal akhir.');
        $query = StockMovement::with(['product', 'user', 'batch'])->whereBetween('moved_at', [$from.' 00:00:00', $to.' 23:59:59']);
        $query->when($request->filled('type'), fn ($query) => $query->where('type', $request->string('type')->toString()))->when($request->filled('category'), fn ($query) => $query->whereHas('product', fn ($query) => $query->where('category_id', $request->integer('category'))))->when($request->filled('product'), fn ($query) => $query->where('product_id', $request->integer('product')));

        return view('reports.index', ['movements' => $query->latest('moved_at')->get(), 'from' => $from, 'to' => $to, 'type' => $request->string('type')->toString(), 'categories' => Category::orderBy('name')->get(), 'products' => Product::orderBy('name')->get()]);
    }
}
