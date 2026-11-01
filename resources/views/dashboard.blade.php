<x-layouts.app>
    <div class="page-header">
        <div>
            <h1 class="page-title">Dashboard</h1>
            <p class="page-subtitle">Ringkasan persediaan dari {{ $periodStart->format('d M Y') }} hingga hari ini.</p>
        </div>
    </div>

    <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @if (auth()->user()->role === 'owner')
            <a href="{{ route('products.index') }}" class="metric block transition hover:-translate-y-0.5 hover:border-brand-600 hover:shadow-md focus:outline-none focus:ring-3 focus:ring-brand-50">
                <p class="metric-label">Jenis barang</p><p class="metric-value">{{ $productCount }}</p><p class="mt-3 text-xs font-semibold text-brand-700">Lihat master barang →</p>
            </a>
            <a href="{{ route('products.index', ['stock_status' => 'low']) }}" class="metric block transition hover:-translate-y-0.5 hover:border-amber-500 hover:shadow-md focus:outline-none focus:ring-3 focus:ring-amber-100">
                <p class="metric-label">Stok perlu perhatian</p><p class="metric-value text-amber-700">{{ $lowStocks->count() }}</p><p class="mt-3 text-xs font-semibold text-amber-700">Lihat stok menipis →</p>
            </a>
        @else
            <article class="metric"><p class="metric-label">Jenis barang</p><p class="metric-value">{{ $productCount }}</p></article>
            <article class="metric"><p class="metric-label">Stok perlu perhatian</p><p class="metric-value text-amber-700">{{ $lowStocks->count() }}</p></article>
        @endif
        <a href="{{ route('stock-in.create') }}" class="metric block transition hover:-translate-y-0.5 hover:border-emerald-600 hover:shadow-md focus:outline-none focus:ring-3 focus:ring-emerald-100">
            <p class="metric-label">Barang masuk</p><p class="metric-value text-emerald-700">{{ $inTotal }}</p><p class="mt-3 text-xs font-semibold text-emerald-700">Buka penerimaan →</p>
        </a>
        <a href="{{ route('stock-out.create') }}" class="metric block transition hover:-translate-y-0.5 hover:border-rose-600 hover:shadow-md focus:outline-none focus:ring-3 focus:ring-rose-100">
            <p class="metric-label">Barang keluar</p><p class="metric-value text-rose-700">{{ $outTotal }}</p><p class="mt-3 text-xs font-semibold text-rose-700">Buka nota pengeluaran →</p>
        </a>
    </section>

    <section class="mt-6 grid gap-6 lg:grid-cols-2">
        <article class="card"><div class="card-body"><h2 class="font-semibold text-ink">Barang paling banyak keluar</h2><div class="mt-4 divide-y divide-slate-100">@forelse($topOutgoing as $row)<div class="flex items-center justify-between py-3"><span class="text-sm font-medium">{{ $row->product->name }}</span><span class="badge badge-neutral">{{ $row->total_quantity }} keluar</span></div>@empty<p class="empty-state">Belum ada pengeluaran pada periode ini.</p>@endforelse</div></div></article>
        <article class="card"><div class="card-body"><h2 class="font-semibold text-ink">Stok rendah</h2><div class="mt-4 divide-y divide-slate-100">@forelse($lowStocks as $item)<div class="flex items-center justify-between py-3"><span class="text-sm font-medium">{{ $item->name }}</span><span class="badge badge-warning">{{ $item->current_stock }} / min {{ $item->minimum_stock }}</span></div>@empty<p class="empty-state">Semua stok berada di atas batas minimum.</p>@endforelse</div></div></article>
    </section>

    <section class="card mt-6"><div class="card-body border-b border-slate-100"><h2 class="font-semibold text-ink">Aktivitas terbaru</h2></div><div class="data-table"><table><thead><tr><th>Waktu</th><th>Barang</th><th>Jenis</th><th>Jumlah</th><th>Petugas</th></tr></thead><tbody>@forelse($recent as $row)<tr><td class="whitespace-nowrap text-slate-500">{{ $row->moved_at->format('d M Y, H:i') }}</td><td class="font-medium">{{ $row->product->name }}</td><td><span class="badge {{ $row->type === 'in' ? 'badge-success' : 'badge-danger' }}">{{ $row->type === 'in' ? 'Masuk' : 'Keluar' }}</span></td><td class="font-semibold">{{ $row->type === 'in' ? '+' : '-' }}{{ $row->quantity }}</td><td>{{ $row->user->name }}</td></tr>@empty<tr><td colspan="5" class="empty-state">Belum ada aktivitas.</td></tr>@endforelse</tbody></table></div></section>
</x-layouts.app>
