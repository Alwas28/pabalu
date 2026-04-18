<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\OwnerAccountController;
use App\Http\Controllers\GuideController;
use App\Http\Controllers\OwnerPaymentMethodController;
use App\Http\Controllers\OwnerPaymentSettingController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ClosingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\OrderQueueController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicOrderController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\StockOpeningController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\Auth\OwnerRegistrationController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $outlets = \App\Models\Outlet::where('is_active', true)
        ->orderByDesc('id')
        ->limit(60)
        ->pluck('nama');
    return view('welcome', compact('outlets'));
});

// ── Owner Self-Registration ─────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/daftar',  [OwnerRegistrationController::class, 'create'])->name('owner.register');
    Route::post('/daftar', [OwnerRegistrationController::class, 'store'])->name('owner.register.store');
});

// ── Midtrans Webhook (no auth, no CSRF) ────────────────────
Route::post('/payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');

// ── Public Online Order (no auth) ──────────────────────────
Route::prefix('order')->name('order.')->group(function () {
    Route::get('/{slug}',        [PublicOrderController::class, 'show'])->name('show');
    Route::post('/{slug}',       [PublicOrderController::class, 'store'])->name('store');
    Route::get('/status/{number}', [PublicOrderController::class, 'status'])->name('status');
});

// Halaman akun suspended/expired (auth tapi tidak perlu account.access)
Route::middleware(['auth', 'verified'])->get('/account-suspended', function () {
    return view('account-suspended');
})->name('account.suspended');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'account.access'])->name('dashboard');

Route::middleware(['auth', 'verified', 'account.access'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // RBAC — Role Management
    Route::middleware('can:role.read')->prefix('rbac')->name('rbac.')->group(function () {
        Route::get('/roles',                [RoleController::class, 'index'])->name('roles.index');
        Route::get('/roles/create',         [RoleController::class, 'create'])->middleware('can:role.create')->name('roles.create');
        Route::post('/roles',               [RoleController::class, 'store'])->middleware('can:role.create')->name('roles.store');
        Route::get('/roles/{role}/edit',    [RoleController::class, 'edit'])->middleware('can:role.update')->name('roles.edit');
        Route::put('/roles/{role}',         [RoleController::class, 'update'])->middleware('can:role.update')->name('roles.update');
        Route::delete('/roles/{role}',      [RoleController::class, 'destroy'])->middleware('can:role.delete')->name('roles.destroy');
    });

    // User Management
    Route::middleware('can:user.read')->prefix('users')->name('users.')->group(function () {
        Route::get('/',                      [UserController::class, 'index'])->name('index');
        Route::get('/create',                [UserController::class, 'create'])->middleware('can:user.create')->name('create');
        Route::post('/',                     [UserController::class, 'store'])->middleware('can:user.create')->name('store');
        Route::get('/{user}/edit',           [UserController::class, 'edit'])->middleware('can:user.update')->name('edit');
        Route::put('/{user}',                [UserController::class, 'update'])->middleware('can:user.update')->name('update');
        Route::delete('/{user}',             [UserController::class, 'destroy'])->middleware('can:user.delete')->name('destroy');
        Route::post('/{user}/verify-email',  [UserController::class, 'verifyEmail'])->middleware('can:user.update')->name('verify-email');
        Route::get('/{user}/owner-detail',   [UserController::class, 'ownerDetail'])->name('owner-detail');
    });

    // Outlet Management
    Route::middleware('can:outlet.read')->prefix('outlets')->name('outlets.')->group(function () {
        Route::get('/',              [OutletController::class, 'index'])->name('index');
        Route::get('/create',        [OutletController::class, 'create'])->middleware('can:outlet.create')->name('create');
        Route::post('/',             [OutletController::class, 'store'])->middleware('can:outlet.create')->name('store');
        Route::get('/{outlet}/edit', [OutletController::class, 'edit'])->middleware('can:outlet.update')->name('edit');
        Route::put('/{outlet}',      [OutletController::class, 'update'])->middleware('can:outlet.update')->name('update');
        Route::delete('/{outlet}',   [OutletController::class, 'destroy'])->middleware('can:outlet.delete')->name('destroy');
    });

    // Category Management
    Route::middleware('can:category.read')->prefix('categories')->name('categories.')->group(function () {
        Route::get('/',                  [CategoryController::class, 'index'])->name('index');
        Route::get('/create',            [CategoryController::class, 'create'])->middleware('can:category.create')->name('create');
        Route::post('/',                 [CategoryController::class, 'store'])->middleware('can:category.create')->name('store');
        Route::get('/{category}/edit',   [CategoryController::class, 'edit'])->middleware('can:category.update')->name('edit');
        Route::put('/{category}',        [CategoryController::class, 'update'])->middleware('can:category.update')->name('update');
        Route::delete('/{category}',     [CategoryController::class, 'destroy'])->middleware('can:category.delete')->name('destroy');
    });

    // Product Management
    Route::middleware('can:product.read')->prefix('products')->name('products.')->group(function () {
        Route::get('/',              [ProductController::class, 'index'])->name('index');
        Route::get('/create',        [ProductController::class, 'create'])->middleware('can:product.create')->name('create');
        Route::post('/',             [ProductController::class, 'store'])->middleware('can:product.create')->name('store');
        Route::get('/{product}/edit',[ProductController::class, 'edit'])->middleware('can:product.update')->name('edit');
        Route::put('/{product}',     [ProductController::class, 'update'])->middleware('can:product.update')->name('update');
        Route::delete('/{product}',  [ProductController::class, 'destroy'])->middleware('can:product.delete')->name('destroy');
    });

    // Stok & Pergerakan
    Route::middleware('can:stock.read')->get('/stock', [StockController::class, 'index'])->name('stock.index');

    // Opening Stok
    Route::middleware('can:stock.opening')->prefix('opening')->name('opening.')->group(function () {
        Route::get('/',  [StockOpeningController::class, 'index'])->name('index');
        Route::post('/', [StockOpeningController::class, 'store'])->name('store');
    });

    // Tambah Stok (stock.in)
    Route::middleware('can:stock.in')->group(function () {
        Route::get('/stock/in',          fn(\Illuminate\Http\Request $r) => app(StockMovementController::class)->create($r, 'in'))->name('stock.in');
        Route::post('/stock/in',         fn(\Illuminate\Http\Request $r) => app(StockMovementController::class)->store($r, 'in'))->name('stock.in.store');
        Route::get('/stock/in/history',  fn(\Illuminate\Http\Request $r) => app(StockMovementController::class)->history($r, 'in'))->name('stock.in.history');
    });

    // Waste / Barang Rusak (stock.waste)
    Route::middleware('can:stock.waste')->group(function () {
        Route::get('/stock/waste',         fn(\Illuminate\Http\Request $r) => app(StockMovementController::class)->create($r, 'waste'))->name('stock.waste');
        Route::post('/stock/waste',        fn(\Illuminate\Http\Request $r) => app(StockMovementController::class)->store($r, 'waste'))->name('stock.waste.store');
        Route::get('/stock/waste/history', fn(\Illuminate\Http\Request $r) => app(StockMovementController::class)->history($r, 'waste'))->name('stock.waste.history');
    });

    // Pengeluaran
    Route::middleware('can:expense.read')->prefix('expenses')->name('expenses.')->group(function () {
        Route::get('/',                   [ExpenseController::class, 'index'])->name('index');
        Route::post('/',                  [ExpenseController::class, 'store'])->middleware('can:expense.create')->name('store');
        Route::put('/{expense}',          [ExpenseController::class, 'update'])->middleware('can:expense.update')->name('update');
        Route::delete('/{expense}',       [ExpenseController::class, 'destroy'])->middleware('can:expense.delete')->name('destroy');
    });

    // Closing Harian
    Route::middleware('can:closing.read')->get('/closing', [ClosingController::class, 'index'])->name('closing.index');

    // Laporan
    Route::middleware('can:report.outlet')->prefix('reports')->name('reports.')->group(function () {
        Route::get('/sales',        [ReportController::class, 'sales'])->name('sales');
        Route::get('/stock',        [ReportController::class, 'stock'])->name('stock');
        Route::get('/profit-loss',  [ReportController::class, 'profitLoss'])->name('profit-loss');
    });

    // Log Aktivitas
    Route::middleware('can:log.read')->get('/activity-logs', [ActivityLogController::class, 'index'])->name('activity-logs.index');

    // Admin — Akun Owner
    Route::middleware('can:user.read')->prefix('admin/owner-accounts')->name('admin.owner-accounts.')->group(function () {
        Route::get('/',                         [OwnerAccountController::class, 'index'])->name('index');
        Route::post('/{user}/set-premium',      [OwnerAccountController::class, 'setPremium'])->name('set-premium');
        Route::post('/{user}/set-trial',        [OwnerAccountController::class, 'setTrial'])->name('set-trial');
        Route::post('/{user}/deactivate',         [OwnerAccountController::class, 'deactivate'])->name('deactivate');
        Route::post('/{user}/activate',           [OwnerAccountController::class, 'activate'])->name('activate');
        Route::post('/{user}/payment-settings',   [OwnerAccountController::class, 'setPaymentSettings'])->name('payment-settings');
    });

    // Panduan Penggunaan
    Route::middleware('can:guide.read')->get('/guide', [GuideController::class, 'index'])->name('guide.index');
    Route::middleware('can:guide.update')->get('/guide/edit', [GuideController::class, 'edit'])->name('guide.edit');
    Route::middleware('can:guide.update')->put('/guide', [GuideController::class, 'update'])->name('guide.update');

    // Metode Pembayaran Owner
    Route::middleware('can:outlet.read')->prefix('owner/payment-methods')->name('owner.payment-methods.')->group(function () {
        Route::get('/',  [OwnerPaymentMethodController::class, 'index'])->name('index');
        Route::put('/',  [OwnerPaymentMethodController::class, 'update'])->name('update');
    });

    // Pengaturan Pembayaran Owner (hanya owner, tidak butuh setting.read)
    Route::middleware('can:outlet.read')->prefix('owner/payment-settings')->name('owner.payment-settings.')->group(function () {
        Route::get('/',  [OwnerPaymentSettingController::class, 'index'])->name('index');
        Route::put('/',  [OwnerPaymentSettingController::class, 'update'])->name('update');
    });

    // Pengaturan Sistem
    Route::middleware('can:setting.read')->prefix('settings')->name('settings.')->group(function () {
        Route::get('/',  [SettingController::class, 'index'])->name('index');
        Route::put('/',  [SettingController::class, 'update'])->middleware('can:setting.update')->name('update');
    });

    // Antrian Order Online
    Route::middleware('can:order.read')->prefix('orders')->name('orders.')->group(function () {
        Route::get('/',                  [OrderQueueController::class, 'index'])->name('index');
        Route::post('/{order}/advance',  [OrderQueueController::class, 'advance'])->middleware('can:order.manage')->name('advance');
        Route::post('/{order}/cancel',   [OrderQueueController::class, 'cancel'])->middleware('can:order.manage')->name('cancel');
        Route::get('/poll',              [OrderQueueController::class, 'poll'])->name('poll');
    });

    // Transaksi / POS
    Route::middleware('can:transaction.read')->prefix('transactions')->name('transactions.')->group(function () {
        Route::get('/pos',                [TransactionController::class, 'pos'])->middleware('can:transaction.create')->name('pos');
        Route::post('/',                  [TransactionController::class, 'store'])->middleware('can:transaction.create')->name('store');
        Route::get('/',                   [TransactionController::class, 'index'])->name('index');
        Route::get('/{transaction}',      [TransactionController::class, 'show'])->name('show');
        Route::post('/{transaction}/void',[TransactionController::class, 'void'])->middleware('can:transaction.void')->name('void');
    });
});

require __DIR__.'/auth.php';
