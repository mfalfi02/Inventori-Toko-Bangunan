<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __invoke()
    {
        $products = Product::withSum('batches as current_stock', 'quantity_remaining')->orderBy('name')->get();

        $periodStart = now()->startOfMonth();
        $movementSummary = StockMovement::query()->where('moved_at', '>=', $periodStart)->selectRaw('type, COUNT(*) as transaction_count, COALESCE(SUM(quantity), 0) as quantity_total')->groupBy('type')->pluck('quantity_total', 'type');
        $topOutgoing = StockMovement::query()->with('product')->where('type', 'out')->where('moved_at', '>=', $periodStart)->select('product_id', DB::raw('SUM(quantity) as total_quantity'))->groupBy('product_id')->orderByDesc('total_quantity')->take(5)->get();

        return view('dashboard', ['productCount' => $products->count(), 'lowStocks' => $products->filter(fn ($p) => $p->current_stock <= $p->minimum_stock), 'recent' => StockMovement::with(['product', 'user'])->latest('moved_at')->take(8)->get(), 'inTotal' => $movementSummary->get('in', 0), 'outTotal' => $movementSummary->get('out', 0), 'topOutgoing' => $topOutgoing, 'periodStart' => $periodStart]);
    }
}
