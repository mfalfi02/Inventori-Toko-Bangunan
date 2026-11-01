<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\StockInController;
use App\Http\Controllers\StockOutController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->name('login.store');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::middleware('role:owner')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('products.index');
        Route::post('/products', [ProductController::class, 'store'])->name('products.store');
        Route::put('/products/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    });
    Route::middleware('role:owner,staff')->group(function () {
        Route::get('/stock-in', [StockInController::class, 'create'])->name('stock-in.create');
        Route::post('/stock-in', [StockInController::class, 'store'])->name('stock-in.store');
        Route::get('/stock-out', [StockOutController::class, 'create'])->name('stock-out.create');
    });
    Route::middleware('role:owner')->group(function () {
        Route::post('/stock-out', [StockOutController::class, 'store'])->name('stock-out.store');
        Route::post('/stock-out/{transaction}/validate', [StockOutController::class, 'validateTransaction'])->name('stock-out.validate');
    });
    Route::middleware('role:staff')->post('/stock-out/{transaction}/confirm', [StockOutController::class, 'confirm'])->name('stock-out.confirm');
});
