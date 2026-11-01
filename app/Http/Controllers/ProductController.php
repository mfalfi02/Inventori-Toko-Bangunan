<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockBatch;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $stockSubquery = StockBatch::query()->selectRaw('COALESCE(SUM(quantity_remaining), 0)')->whereColumn('product_id', 'products.id');
        $stockSql = '('.$stockSubquery->toSql().')';
        $stockBindings = $stockSubquery->getBindings();

        $products = Product::query()
            ->with('category')
            ->withSum('batches as stock', 'quantity_remaining')
            ->when($request->filled('search'), fn ($query) => $query->where(fn ($query) => $query->where('sku', 'like', '%'.$request->string('search').'%')->orWhere('name', 'like', '%'.$request->string('search').'%')))
            ->when($request->filled('category'), fn ($query) => $query->where('category_id', $request->integer('category')))
            ->when($request->string('stock_status')->toString() === 'empty', fn ($query) => $query->whereRaw($stockSql.' = 0', $stockBindings))
            ->when($request->string('stock_status')->toString() === 'low', fn ($query) => $query->whereRaw($stockSql.' > 0 AND '.$stockSql.' <= minimum_stock', [...$stockBindings, ...$stockBindings]))
            ->when($request->string('stock_status')->toString() === 'available', fn ($query) => $query->whereRaw($stockSql.' > minimum_stock', $stockBindings))
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('products.index', ['products' => $products, 'categories' => Category::orderBy('name')->get()]);
    }

    public function store(StoreProductRequest $request)
    {
        Product::create($request->validated());

        return back()->with('success', 'Barang berhasil ditambahkan.');
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $product->update($request->validated());

        return back()->with('success', 'Data barang diperbarui.');
    }
}
