<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Bintang Prima</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="app-shell">
        <header class="topbar">
            <div class="page-wrap flex min-h-16 items-center justify-between gap-4 py-3">
                <a href="{{ route('dashboard') }}" class="brand"><span class="brand-mark">BP</span><span>Bintang Prima</span></a>
                <nav class="hidden items-center gap-1 lg:flex">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'nav-link-active' : '' }}" href="{{ route('dashboard') }}">Dashboard</a>
                    @if (auth()->user()->role === 'owner')
                        <a class="nav-link {{ request()->routeIs('products.*') ? 'nav-link-active' : '' }}" href="{{ route('products.index') }}">Master Barang</a>
                        <a class="nav-link {{ request()->routeIs('reports.*') ? 'nav-link-active' : '' }}" href="{{ route('reports.index') }}">Laporan</a>
                    @endif
                    <a class="nav-link {{ request()->routeIs('stock-in.*') ? 'nav-link-active' : '' }}" href="{{ route('stock-in.create') }}">Barang Masuk</a>
                    <a class="nav-link {{ request()->routeIs('stock-out.*') ? 'nav-link-active' : '' }}" href="{{ route('stock-out.create') }}">Barang Keluar</a>
                </nav>
                <details class="relative lg:hidden"><summary class="btn-secondary cursor-pointer list-none">Menu</summary><nav class="absolute right-0 mt-2 grid w-52 gap-1 rounded-lg border border-slate-200 bg-white p-2 shadow-lg"><a class="nav-link" href="{{ route('dashboard') }}">Dashboard</a>@if (auth()->user()->role === 'owner')<a class="nav-link" href="{{ route('products.index') }}">Master Barang</a><a class="nav-link" href="{{ route('reports.index') }}">Laporan</a>@endif<a class="nav-link" href="{{ route('stock-in.create') }}">Barang Masuk</a><a class="nav-link" href="{{ route('stock-out.create') }}">Barang Keluar</a></nav></details>
                <div class="hidden items-center gap-3 lg:flex"><div class="text-right"><p class="text-sm font-semibold text-ink">{{ auth()->user()->name }}</p><p class="text-xs text-slate-500">{{ auth()->user()->role === 'owner' ? 'Pemilik toko' : 'Staf gudang' }}</p></div><form action="{{ route('logout') }}" method="POST">@csrf<button class="btn-secondary" type="submit">Keluar</button></form></div>
            </div>
        </header>
        <main class="page-wrap">
            @if (session('success'))<div class="notice-success"><span class="font-semibold">Berhasil.</span><span>{{ session('success') }}</span></div>@endif
            @if ($errors->any())<div class="notice-error"><p class="font-semibold">Periksa kembali data yang diisi.</p><ul class="mt-2 list-disc pl-5">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
            {{ $slot }}
        </main>
    </div>
</body>
</html>
