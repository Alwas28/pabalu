<?php

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\DashboardController;
use App\Http\Controllers\Api\V1\LaundryController;
use App\Http\Controllers\Api\V1\OutletController;
use App\Http\Controllers\Api\V1\PosController;
use App\Http\Controllers\Api\V1\ShiftController;
use Illuminate\Support\Facades\Route;

// API untuk aplikasi kasir mobile (React Native). Auth pakai token Sanctum (Bearer),
// bukan session — lihat routes/web.php untuk versi web (server-rendered Blade).
Route::prefix('v1')->name('api.v1.')->group(function () {

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('dynamic.throttle:login')
        ->name('login');

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');

        Route::get('outlets', [OutletController::class, 'index'])->name('outlets.index');

        Route::prefix('outlets/{outlet}')->name('outlets.')->group(function () {
            Route::get('dashboard', [DashboardController::class, 'show'])->name('dashboard');

            Route::get('shift', [ShiftController::class, 'show'])->name('shift.show');
            Route::post('shift/open', [ShiftController::class, 'open'])->name('shift.open');
            Route::post('shift/close', [ShiftController::class, 'close'])->name('shift.close');

            Route::get('categories', [PosController::class, 'categories'])->name('categories');
            Route::get('products', [PosController::class, 'products'])->name('products');

            Route::get('transactions', [PosController::class, 'index'])->name('transactions.index');
            Route::post('transactions', [PosController::class, 'store'])->name('transactions.store');
            Route::get('transactions/{transaction}', [PosController::class, 'show'])->name('transactions.show');

            // Laundry — alur order bertahap (masuk -> proses -> selesai -> diambil+bayar),
            // beda bentuk dari Kasir F&B/Retail/Salon di atas.
            Route::get('laundry/products', [LaundryController::class, 'products'])->name('laundry.products');
            Route::get('laundry/orders', [LaundryController::class, 'index'])->name('laundry.orders.index');
            Route::post('laundry/orders', [LaundryController::class, 'store'])->name('laundry.orders.store');
            Route::get('laundry/orders/{laundryOrder}', [LaundryController::class, 'show'])->name('laundry.orders.show');
            Route::post('laundry/orders/{laundryOrder}/advance', [LaundryController::class, 'updateStatus'])->name('laundry.orders.advance');
            Route::post('laundry/orders/{laundryOrder}/pay', [LaundryController::class, 'pay'])->name('laundry.orders.pay');
        });
    });
});
