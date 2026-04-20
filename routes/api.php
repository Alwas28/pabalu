<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ClosingApiController;
use App\Http\Controllers\Api\ExpenseApiController;
use App\Http\Controllers\Api\OrderApiController;
use App\Http\Controllers\Api\OutletApiController;
use App\Http\Controllers\Api\ProductApiController;
use App\Http\Controllers\Api\ReportApiController;
use App\Http\Controllers\Api\StockApiController;
use App\Http\Controllers\Api\TransactionApiController;
use App\Http\Controllers\Api\UserApiController;
use Illuminate\Support\Facades\Route;

// ── Public: Authentication ──────────────────────────────
Route::post('/auth/login', [AuthController::class, 'login'])->name('api.auth.login');

// ── Protected: Butuh token Sanctum ─────────────────────
Route::middleware('auth:sanctum')->group(function () {

    // Auth
    Route::post('/auth/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
    Route::get('/auth/me',      [AuthController::class, 'me'])->name('api.auth.me');

    // Outlets
    Route::get('/outlets', [OutletApiController::class, 'index'])->name('api.outlets.index');

    // Products (POS)
    Route::get('/products', [ProductApiController::class, 'index'])->name('api.products.index');

    // Products (Kelola)
    Route::prefix('manage/products')->name('api.manage.products.')->group(function () {
        Route::get('/',         [ProductApiController::class, 'manage'])->name('index');
        Route::post('/',        [ProductApiController::class, 'store'])->name('store');
        Route::put('/{product}',    [ProductApiController::class, 'update'])->name('update');
        Route::delete('/{product}', [ProductApiController::class, 'destroy'])->name('destroy');
    });

    // Transactions (POS)
    Route::prefix('transactions')->name('api.transactions.')->group(function () {
        Route::get('/',                    [TransactionApiController::class, 'index'])->name('index');
        Route::post('/',                   [TransactionApiController::class, 'store'])->name('store');
        Route::get('/config',              [TransactionApiController::class, 'config'])->name('config');
        Route::post('/snap-token',         [TransactionApiController::class, 'snapToken'])->name('snap-token');
        Route::post('/qris-charge',        [TransactionApiController::class, 'qrisCharge'])->name('qris-charge');
        Route::get('/payment-status',      [TransactionApiController::class, 'checkPaymentStatus'])->name('payment-status');
        Route::get('/{transaction}',       [TransactionApiController::class, 'show'])->name('show');
    });

    // Stock
    Route::prefix('stock')->name('api.stock.')->group(function () {
        Route::get('/',          [StockApiController::class, 'index'])->name('index');
        Route::get('/opening',   [StockApiController::class, 'opening'])->name('opening');
        Route::post('/opening',  [StockApiController::class, 'storeOpening'])->name('opening.store');
        Route::post('/in',       [StockApiController::class, 'storeIn'])->name('in');
        Route::post('/waste',    [StockApiController::class, 'storeWaste'])->name('waste');
        Route::get('/history',   [StockApiController::class, 'history'])->name('history');
    });

    // Expenses (Pengeluaran)
    Route::prefix('expenses')->name('api.expenses.')->group(function () {
        Route::get('/',              [ExpenseApiController::class, 'index'])->name('index');
        Route::post('/',             [ExpenseApiController::class, 'store'])->name('store');
        Route::put('/{expense}',     [ExpenseApiController::class, 'update'])->name('update');
        Route::delete('/{expense}',  [ExpenseApiController::class, 'destroy'])->name('destroy');
    });

    // Orders (Antrian)
    Route::prefix('orders')->name('api.orders.')->group(function () {
        Route::get('/',             [OrderApiController::class, 'index'])->name('index');
        Route::get('/poll',         [OrderApiController::class, 'poll'])->name('poll');
        Route::post('/{order}/advance', [OrderApiController::class, 'advance'])->name('advance');
        Route::post('/{order}/cancel',  [OrderApiController::class, 'cancel'])->name('cancel');
    });

    // Closing
    Route::get('/closing', [ClosingApiController::class, 'index'])->name('api.closing.index');

    // Reports
    Route::prefix('reports')->name('api.reports.')->group(function () {
        Route::get('/sales',        [ReportApiController::class, 'sales'])->name('sales');
        Route::get('/profit-loss',  [ReportApiController::class, 'profitLoss'])->name('profit-loss');
    });

    // Users (Kelola)
    Route::prefix('users')->name('api.users.')->group(function () {
        Route::get('/',          [UserApiController::class, 'index'])->name('index');
        Route::post('/',         [UserApiController::class, 'store'])->name('store');
        Route::put('/{user}',    [UserApiController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserApiController::class, 'destroy'])->name('destroy');
    });
});
