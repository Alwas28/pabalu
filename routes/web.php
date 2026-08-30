<?php

use App\Http\Controllers\Fnb;
use App\Http\Controllers\Laundry;
use App\Http\Controllers\Retail;
use App\Http\Controllers\Salon;
use App\Http\Controllers\Sewa;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\OutletTypeCategoryController;
use App\Http\Controllers\OutletTypeController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\UserController;
use App\Models\Employee;
use App\Models\Outlet;
use App\Models\ProOwnerInvoice;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    $partners = \App\Models\Partner::where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    $outletTypes = \App\Models\OutletType::where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    $featuredProducts = \App\Models\Product::where('is_active', true)
        ->whereNotNull('image')
        ->where('image', '!=', '')
        ->whereHas('outlet.outletType', function ($q) {
            $q->whereIn('slug', ['warung_makan', 'kafe', 'retail']);
        })
        ->with('outlet.outletType')
        ->latest()
        ->take(8)
        ->get();

    $registeredOutlets = Outlet::where('is_active', true)
        ->with('outletType')
        ->orderBy('name')
        ->take(8)
        ->get();

    $sliders = \App\Models\HomeSlider::where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();

    $featuredCategories = \App\Models\Category::where('is_active', true)
        ->where('is_featured', true)
        ->orderBy('sort_order')
        ->orderBy('name')
        ->get();

    $featuredCategoryProducts = \App\Models\Product::where('is_active', true)
        ->whereIn('category_id', $featuredCategories->pluck('id'))
        ->whereHas('outlet', fn ($q) => $q->where('is_active', true))
        ->with('outlet.outletType', 'category')
        ->orderBy('name')
        ->limit(10)
        ->get();

    $promoBanners = \App\Models\PromoBanner::where('is_active', true)
        ->orderBy('sort_order')
        ->orderBy('id')
        ->get();

    // Produk terlaris ASLI (bukan contoh) — dihitung dari total qty terjual lintas
    // seluruh transaksi selesai, bukan cuma "hari ini" (platform masih baru, data
    // per-hari terlalu tipis buat jadi ranking yang berarti).
    $bestSellerIds = \App\Models\TransactionItem::query()
        ->select('product_id')
        ->selectRaw('SUM(qty) as total_qty')
        ->whereNotNull('product_id')
        ->whereHas('transaction', fn ($q) => $q->where('status', 'completed'))
        ->groupBy('product_id')
        ->orderByDesc('total_qty')
        ->limit(4)
        ->pluck('product_id');

    $bestSellers = \App\Models\Product::whereIn('id', $bestSellerIds)
        ->where('is_active', true)
        ->with('outlet.outletType', 'category')
        ->get()
        ->sortBy(fn ($p) => array_search($p->id, $bestSellerIds->all()))
        ->values();

    return view('welcome', compact('partners', 'outletTypes', 'featuredProducts', 'registeredOutlets', 'sliders', 'featuredCategories', 'featuredCategoryProducts', 'promoBanners', 'bestSellers'));
});

// ── Halaman statis (CMS pages, publik) ──────────────────────────────────────
Route::get('/halaman/{slug}', [\App\Http\Controllers\PageController::class, 'show'])->name('pages.show');

// ── Pencarian produk & outlet (publik) ──────────────────────────────────────
Route::get('/pencarian', [\App\Http\Controllers\Public\SearchController::class, 'index'])->name('search.index');

// ── Kategori homepage (publik) — produk dari semua kategori yang tergabung ──
Route::get('/kategori/{homeCategory}', [\App\Http\Controllers\Public\HomeCategoryController::class, 'show'])->name('home-categories.show');

// ── Kategori produk tunggal (publik) — dipakai kartu "Kategori Pilihan" ─────
Route::get('/produk/kategori/{category}', [\App\Http\Controllers\Public\CategoryController::class, 'show'])->name('product-categories.show');

// ── Public self-ordering F&B (no auth) ─────────────────────────────────────
Route::get('/menu/{code}', [\App\Http\Controllers\Public\MenuController::class, 'index'])->name('public.menu');
Route::post('/menu/{code}/order', [\App\Http\Controllers\Public\MenuController::class, 'placeOrder'])->name('public.menu.order');
Route::get('/menu/{code}/pesanan/{token}', [\App\Http\Controllers\Public\MenuController::class, 'track'])
    ->middleware('dynamic.throttle:public_lookup')
    ->name('public.menu.track');

// ── Public katalog produk Retail (no auth, read-only) ──────────────────────
Route::get('/katalog/{code}', [\App\Http\Controllers\Public\CatalogController::class, 'index'])->name('public.katalog');
// ────────────────────────────────────────────────────────────────────────────

// Setup wizard (auth + verified, tanpa require.setup agar tidak infinite redirect)
Route::middleware(['auth', 'verified'])->prefix('setup')->name('setup.')->group(function () {
    Route::get('/',        [SetupController::class, 'index'])->name('index');
    Route::post('/profile',[SetupController::class, 'saveProfile'])->name('profile');
    Route::get('/outlet',  [SetupController::class, 'outlet'])->name('outlet');
    Route::post('/outlet', [SetupController::class, 'saveOutlet'])->name('outlet.save');
    Route::get('/done',    [SetupController::class, 'done'])->name('done');
});

// ── F&B — warung makan, kafe (prefix: fnb/)
// Termasuk orders/kitchen mode, opening/closing stok.
$fnbRoutes = function () {
    Route::get('{outlet}/settings',  [Fnb\SettingController::class, 'edit'])->name('settings.edit');
    Route::patch('{outlet}/settings',[Fnb\SettingController::class, 'update'])->name('settings.update');

    Route::get('{outlet}/shifts',                        [Fnb\ShiftController::class, 'index'])->name('shifts.index');
    Route::post('{outlet}/shifts',                       [Fnb\ShiftController::class, 'store'])->name('shifts.store');
    Route::patch('{outlet}/shifts/{shift}/close',        [Fnb\ShiftController::class, 'close'])->name('shifts.close');
    Route::patch('{outlet}/shifts/{shift}',              [Fnb\ShiftController::class, 'update'])->name('shifts.update');
    Route::delete('{outlet}/shifts/{shift}',             [Fnb\ShiftController::class, 'destroy'])->name('shifts.destroy');

    Route::get('{outlet}/categories', [Fnb\CategoryController::class, 'index'])->name('categories.index');

    Route::get('{outlet}/products',                              [Fnb\ProductController::class, 'index'])->name('products.index');
    Route::post('{outlet}/products',                             [Fnb\ProductController::class, 'store'])->name('products.store');
    Route::match(['put','patch'],'{outlet}/products/{product}',  [Fnb\ProductController::class, 'update'])->name('products.update');
    Route::delete('{outlet}/products/{product}',                 [Fnb\ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('{outlet}/products/{product}/toggle-active',     [Fnb\ProductController::class, 'toggleActive'])->name('products.toggle-active');

    Route::get('{outlet}/pos',  [Fnb\PosController::class, 'index'])->name('pos.index');
    Route::post('{outlet}/pos', [Fnb\PosController::class, 'store'])->name('pos.store');

    // Orders/Kitchen — khusus F&B
    Route::get('{outlet}/orders',                              [Fnb\OrderController::class, 'index'])->name('orders.index');
    Route::post('{outlet}/orders',                             [Fnb\OrderController::class, 'store'])->name('orders.store');
    Route::get('{outlet}/orders/{order}',                      [Fnb\OrderController::class, 'show'])->name('orders.show');
    Route::post('{outlet}/orders/{order}/serve-item/{item}',   [Fnb\OrderController::class, 'serveItem'])->name('orders.serve-item');
    Route::patch('{outlet}/orders/{order}/status',             [Fnb\OrderController::class, 'updateStatus'])->name('orders.status');
    Route::post('{outlet}/orders/{order}/pay',                 [Fnb\OrderController::class, 'pay'])->name('orders.pay');
    Route::post('{outlet}/orders/{order}/qris-pay/charge',     [Fnb\OrderController::class, 'qrisCharge'])->name('orders.qris-pay.charge');
    Route::get('{outlet}/orders/{order}/qris-pay/status',      [Fnb\OrderController::class, 'qrisStatus'])->name('orders.qris-pay.status');
    Route::get('{outlet}/orders/{order}/kitchen',              [Fnb\OrderController::class, 'kitchen'])->name('orders.kitchen');
    Route::get('{outlet}/orders/{order}/bill',                 [Fnb\OrderController::class, 'bill'])->name('orders.bill');
    Route::get('{outlet}/orders-lookup',                       [Fnb\OrderController::class, 'lookup'])->name('orders.lookup');

    Route::get('{outlet}/transactions',                       [Fnb\TransactionController::class, 'index'])->name('transactions.index');
    Route::get('{outlet}/transactions/{transaction}',         [Fnb\TransactionController::class, 'show'])->name('transactions.show');
    Route::get('{outlet}/transactions/{transaction}/receipt', [Fnb\TransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::patch('{outlet}/transactions/{transaction}/void',  [Fnb\TransactionController::class, 'void'])->name('transactions.void');

    Route::get('{outlet}/stock', [Fnb\StockViewController::class, 'index'])->name('stock.index');

    // Opening & Closing Stok — khusus F&B
    Route::get('{outlet}/opening',                  [Fnb\OpeningStockController::class, 'index'])->name('opening.index');
    Route::post('{outlet}/opening',                 [Fnb\OpeningStockController::class, 'store'])->name('opening.store');
    Route::get('{outlet}/opening/{stockOpname}',    [Fnb\OpeningStockController::class, 'show'])->name('opening.show');
    Route::delete('{outlet}/opening/{stockOpname}', [Fnb\OpeningStockController::class, 'destroy'])->name('opening.destroy');

    Route::get('{outlet}/closing',    [Fnb\DailyClosingController::class, 'index'])->name('closing.index');
    Route::post('{outlet}/closing',   [Fnb\DailyClosingController::class, 'store'])->name('closing.store');
    Route::delete('{outlet}/closing', [Fnb\DailyClosingController::class, 'destroy'])->name('closing.destroy');

    Route::get('{outlet}/expenses',                     [Fnb\ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('{outlet}/expenses',                    [Fnb\ExpenseController::class, 'store'])->name('expenses.store');
    Route::patch('{outlet}/expenses/{expense}',         [Fnb\ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('{outlet}/expenses/{expense}',        [Fnb\ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('{outlet}/expense-categories',          [Fnb\ExpenseController::class, 'storeCategory'])->name('expense-categories.store');
    Route::delete('{outlet}/expense-categories/{slug}', [Fnb\ExpenseController::class, 'destroyCategory'])->name('expense-categories.destroy');

    Route::get('{outlet}/stock-in',                [Fnb\StockInController::class, 'index'])->name('stock-in.index');
    Route::get('{outlet}/stock-in/create',         [Fnb\StockInController::class, 'create'])->name('stock-in.create');
    Route::post('{outlet}/stock-in',               [Fnb\StockInController::class, 'store'])->name('stock-in.store');
    Route::get('{outlet}/stock-in/{stockIn}',      [Fnb\StockInController::class, 'show'])->name('stock-in.show');
    Route::get('{outlet}/stock-in/{stockIn}/edit', [Fnb\StockInController::class, 'edit'])->name('stock-in.edit');
    Route::patch('{outlet}/stock-in/{stockIn}',    [Fnb\StockInController::class, 'update'])->name('stock-in.update');
    Route::delete('{outlet}/stock-in/{stockIn}',   [Fnb\StockInController::class, 'destroy'])->name('stock-in.destroy');

    Route::get('{outlet}/waste',            [Fnb\WasteController::class, 'index'])->name('waste.index');
    Route::get('{outlet}/waste/create',     [Fnb\WasteController::class, 'create'])->name('waste.create');
    Route::post('{outlet}/waste',           [Fnb\WasteController::class, 'store'])->name('waste.store');
    Route::get('{outlet}/waste/{waste}',    [Fnb\WasteController::class, 'show'])->name('waste.show');
    Route::delete('{outlet}/waste/{waste}', [Fnb\WasteController::class, 'destroy'])->name('waste.destroy');

    Route::get('{outlet}/reports/sales',        [Fnb\SalesReportController::class, 'index'])->name('reports.sales');
    Route::get('{outlet}/reports/sales/export', [Fnb\SalesReportController::class, 'export'])->name('reports.sales.export');
    Route::get('{outlet}/reports/profit-loss',  [Fnb\ProfitLossController::class, 'index'])->name('reports.profit-loss');

    // Pesanan mandiri dari QR menu — inbox & review sebelum masuk antrian
    Route::get('{outlet}/self-orders',                        [\App\Http\Controllers\SelfOrderController::class, 'index'])->name('self-orders.index');
    Route::get('{outlet}/self-orders/{order}',                [\App\Http\Controllers\SelfOrderController::class, 'show'])->name('self-orders.show');
    Route::post('{outlet}/self-orders/{order}/approve',       [\App\Http\Controllers\SelfOrderController::class, 'approve'])->name('self-orders.approve');
    Route::patch('{outlet}/self-orders/{order}/reject',       [\App\Http\Controllers\SelfOrderController::class, 'reject'])->name('self-orders.reject');
    Route::delete('{outlet}/self-orders/{order}',             [\App\Http\Controllers\SelfOrderController::class, 'destroy'])->name('self-orders.destroy');
};

// ── Salon — jasa kecantikan (prefix: salon/)
// Tanpa orders/kitchen, tanpa opening/closing stok, tanpa manajemen HPP.
$salonRoutes = function () {
    Route::get('{outlet}/settings',  [Salon\SettingController::class, 'edit'])->name('settings.edit');
    Route::patch('{outlet}/settings',[Salon\SettingController::class, 'update'])->name('settings.update');

    Route::get('{outlet}/shifts',                  [Salon\ShiftController::class, 'index'])->name('shifts.index');
    Route::post('{outlet}/shifts',                 [Salon\ShiftController::class, 'store'])->name('shifts.store');
    Route::patch('{outlet}/shifts/{shift}/close',  [Salon\ShiftController::class, 'close'])->name('shifts.close');
    Route::patch('{outlet}/shifts/{shift}',        [Salon\ShiftController::class, 'update'])->name('shifts.update');
    Route::delete('{outlet}/shifts/{shift}',       [Salon\ShiftController::class, 'destroy'])->name('shifts.destroy');

    Route::get('{outlet}/categories', [Salon\CategoryController::class, 'index'])->name('categories.index');

    Route::get('{outlet}/products',                             [Salon\ProductController::class, 'index'])->name('products.index');
    Route::post('{outlet}/products',                            [Salon\ProductController::class, 'store'])->name('products.store');
    Route::match(['put','patch'],'{outlet}/products/{product}', [Salon\ProductController::class, 'update'])->name('products.update');
    Route::delete('{outlet}/products/{product}',                [Salon\ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('{outlet}/products/{product}/toggle-active',    [Salon\ProductController::class, 'toggleActive'])->name('products.toggle-active');

    Route::get('{outlet}/pos',  [Salon\PosController::class, 'index'])->name('pos.index');
    Route::post('{outlet}/pos', [Salon\PosController::class, 'store'])->name('pos.store');

    Route::get('{outlet}/transactions',                       [Salon\TransactionController::class, 'index'])->name('transactions.index');
    Route::get('{outlet}/transactions/{transaction}',         [Salon\TransactionController::class, 'show'])->name('transactions.show');
    Route::get('{outlet}/transactions/{transaction}/receipt', [Salon\TransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::patch('{outlet}/transactions/{transaction}/void',  [Salon\TransactionController::class, 'void'])->name('transactions.void');

    Route::get('{outlet}/stock', [Salon\StockViewController::class, 'index'])->name('stock.index');

    Route::get('{outlet}/expenses',                     [Salon\ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('{outlet}/expenses',                    [Salon\ExpenseController::class, 'store'])->name('expenses.store');
    Route::patch('{outlet}/expenses/{expense}',         [Salon\ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('{outlet}/expenses/{expense}',        [Salon\ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('{outlet}/expense-categories',          [Salon\ExpenseController::class, 'storeCategory'])->name('expense-categories.store');
    Route::delete('{outlet}/expense-categories/{slug}', [Salon\ExpenseController::class, 'destroyCategory'])->name('expense-categories.destroy');

    Route::get('{outlet}/stock-in',                [Salon\StockInController::class, 'index'])->name('stock-in.index');
    Route::get('{outlet}/stock-in/create',         [Salon\StockInController::class, 'create'])->name('stock-in.create');
    Route::post('{outlet}/stock-in',               [Salon\StockInController::class, 'store'])->name('stock-in.store');
    Route::get('{outlet}/stock-in/{stockIn}',      [Salon\StockInController::class, 'show'])->name('stock-in.show');
    Route::get('{outlet}/stock-in/{stockIn}/edit', [Salon\StockInController::class, 'edit'])->name('stock-in.edit');
    Route::patch('{outlet}/stock-in/{stockIn}',    [Salon\StockInController::class, 'update'])->name('stock-in.update');
    Route::delete('{outlet}/stock-in/{stockIn}',   [Salon\StockInController::class, 'destroy'])->name('stock-in.destroy');

    Route::get('{outlet}/waste',            [Salon\WasteController::class, 'index'])->name('waste.index');
    Route::get('{outlet}/waste/create',     [Salon\WasteController::class, 'create'])->name('waste.create');
    Route::post('{outlet}/waste',           [Salon\WasteController::class, 'store'])->name('waste.store');
    Route::get('{outlet}/waste/{waste}',    [Salon\WasteController::class, 'show'])->name('waste.show');
    Route::delete('{outlet}/waste/{waste}', [Salon\WasteController::class, 'destroy'])->name('waste.destroy');

    Route::get('{outlet}/reports/sales',        [Salon\SalesReportController::class, 'index'])->name('reports.sales');
    Route::get('{outlet}/reports/sales/export', [Salon\SalesReportController::class, 'export'])->name('reports.sales.export');
    Route::get('{outlet}/reports/profit-loss',  [Salon\ProfitLossController::class, 'index'])->name('reports.profit-loss');

    Route::get('{outlet}/self-orders',                  [\App\Http\Controllers\SelfOrderController::class, 'index'])->name('self-orders.index');
    Route::get('{outlet}/self-orders/{order}',          [\App\Http\Controllers\SelfOrderController::class, 'show'])->name('self-orders.show');
    Route::post('{outlet}/self-orders/{order}/approve', [\App\Http\Controllers\SelfOrderController::class, 'approve'])->name('self-orders.approve');
    Route::patch('{outlet}/self-orders/{order}/reject', [\App\Http\Controllers\SelfOrderController::class, 'reject'])->name('self-orders.reject');
    Route::delete('{outlet}/self-orders/{order}',       [\App\Http\Controllers\SelfOrderController::class, 'destroy'])->name('self-orders.destroy');
};

// ── Laundry — jasa laundry (prefix: laundry/)
// Tanpa orders/kitchen, tanpa opening/closing stok, tanpa manajemen HPP.
$laundryRoutes = function () {
    Route::get('{outlet}/settings',  [Laundry\SettingController::class, 'edit'])->name('settings.edit');
    Route::patch('{outlet}/settings',[Laundry\SettingController::class, 'update'])->name('settings.update');

    Route::get('{outlet}/shifts',                  [Laundry\ShiftController::class, 'index'])->name('shifts.index');
    Route::post('{outlet}/shifts',                 [Laundry\ShiftController::class, 'store'])->name('shifts.store');
    Route::patch('{outlet}/shifts/{shift}/close',  [Laundry\ShiftController::class, 'close'])->name('shifts.close');
    Route::patch('{outlet}/shifts/{shift}',        [Laundry\ShiftController::class, 'update'])->name('shifts.update');
    Route::delete('{outlet}/shifts/{shift}',       [Laundry\ShiftController::class, 'destroy'])->name('shifts.destroy');

    Route::get('{outlet}/categories', [Laundry\CategoryController::class, 'index'])->name('categories.index');

    Route::get('{outlet}/products',                             [Laundry\ProductController::class, 'index'])->name('products.index');
    Route::post('{outlet}/products',                            [Laundry\ProductController::class, 'store'])->name('products.store');
    Route::match(['put','patch'],'{outlet}/products/{product}', [Laundry\ProductController::class, 'update'])->name('products.update');
    Route::delete('{outlet}/products/{product}',                [Laundry\ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('{outlet}/products/{product}/toggle-active',    [Laundry\ProductController::class, 'toggleActive'])->name('products.toggle-active');

    Route::get('{outlet}/pos',  [Laundry\PosController::class, 'index'])->name('pos.index');
    Route::post('{outlet}/pos', [Laundry\PosController::class, 'store'])->name('pos.store');

    Route::get('{outlet}/transactions',                       [Laundry\TransactionController::class, 'index'])->name('transactions.index');
    Route::get('{outlet}/transactions/{transaction}',         [Laundry\TransactionController::class, 'show'])->name('transactions.show');
    Route::get('{outlet}/transactions/{transaction}/receipt', [Laundry\TransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::patch('{outlet}/transactions/{transaction}/void',  [Laundry\TransactionController::class, 'void'])->name('transactions.void');

    Route::get('{outlet}/stock', [Laundry\StockViewController::class, 'index'])->name('stock.index');

    Route::get('{outlet}/expenses',                     [Laundry\ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('{outlet}/expenses',                    [Laundry\ExpenseController::class, 'store'])->name('expenses.store');
    Route::patch('{outlet}/expenses/{expense}',         [Laundry\ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('{outlet}/expenses/{expense}',        [Laundry\ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('{outlet}/expense-categories',          [Laundry\ExpenseController::class, 'storeCategory'])->name('expense-categories.store');
    Route::delete('{outlet}/expense-categories/{slug}', [Laundry\ExpenseController::class, 'destroyCategory'])->name('expense-categories.destroy');

    Route::get('{outlet}/stock-in',                [Laundry\StockInController::class, 'index'])->name('stock-in.index');
    Route::get('{outlet}/stock-in/create',         [Laundry\StockInController::class, 'create'])->name('stock-in.create');
    Route::post('{outlet}/stock-in',               [Laundry\StockInController::class, 'store'])->name('stock-in.store');
    Route::get('{outlet}/stock-in/{stockIn}',      [Laundry\StockInController::class, 'show'])->name('stock-in.show');
    Route::get('{outlet}/stock-in/{stockIn}/edit', [Laundry\StockInController::class, 'edit'])->name('stock-in.edit');
    Route::patch('{outlet}/stock-in/{stockIn}',    [Laundry\StockInController::class, 'update'])->name('stock-in.update');
    Route::delete('{outlet}/stock-in/{stockIn}',   [Laundry\StockInController::class, 'destroy'])->name('stock-in.destroy');

    Route::get('{outlet}/waste',            [Laundry\WasteController::class, 'index'])->name('waste.index');
    Route::get('{outlet}/waste/create',     [Laundry\WasteController::class, 'create'])->name('waste.create');
    Route::post('{outlet}/waste',           [Laundry\WasteController::class, 'store'])->name('waste.store');
    Route::get('{outlet}/waste/{waste}',    [Laundry\WasteController::class, 'show'])->name('waste.show');
    Route::delete('{outlet}/waste/{waste}', [Laundry\WasteController::class, 'destroy'])->name('waste.destroy');

    Route::get('{outlet}/reports/sales',        [Laundry\SalesReportController::class, 'index'])->name('reports.sales');
    Route::get('{outlet}/reports/sales/export', [Laundry\SalesReportController::class, 'export'])->name('reports.sales.export');
    Route::get('{outlet}/reports/profit-loss',  [Laundry\ProfitLossController::class, 'index'])->name('reports.profit-loss');

    Route::get('{outlet}/self-orders',                  [\App\Http\Controllers\SelfOrderController::class, 'index'])->name('self-orders.index');
    Route::get('{outlet}/self-orders/{order}',          [\App\Http\Controllers\SelfOrderController::class, 'show'])->name('self-orders.show');
    Route::post('{outlet}/self-orders/{order}/approve', [\App\Http\Controllers\SelfOrderController::class, 'approve'])->name('self-orders.approve');
    Route::patch('{outlet}/self-orders/{order}/reject', [\App\Http\Controllers\SelfOrderController::class, 'reject'])->name('self-orders.reject');
    Route::delete('{outlet}/self-orders/{order}',       [\App\Http\Controllers\SelfOrderController::class, 'destroy'])->name('self-orders.destroy');

    Route::get('{outlet}/customers/search', [Laundry\CustomerController::class, 'search'])->name('customers.search');

    Route::get('{outlet}/laundry-orders',                         [Laundry\LaundryOrderController::class, 'index'])->name('laundry-orders.index');
    Route::post('{outlet}/laundry-orders',                        [Laundry\LaundryOrderController::class, 'store'])->name('laundry-orders.store');
    Route::get('{outlet}/laundry-orders/{laundryOrder}',          [Laundry\LaundryOrderController::class, 'show'])->name('laundry-orders.show');
    Route::patch('{outlet}/laundry-orders/{laundryOrder}/status', [Laundry\LaundryOrderController::class, 'updateStatus'])->name('laundry-orders.update-status');
    Route::post('{outlet}/laundry-orders/{laundryOrder}/pay',     [Laundry\LaundryOrderController::class, 'pay'])->name('laundry-orders.pay');
    Route::get('{outlet}/laundry-orders/{laundryOrder}/receipt',  [Laundry\LaundryOrderController::class, 'receipt'])->name('laundry-orders.receipt');
    Route::delete('{outlet}/laundry-orders/{laundryOrder}',       [Laundry\LaundryOrderController::class, 'destroy'])->name('laundry-orders.destroy');
};

// ── Sewa — jasa rental (prefix: sewa/)
// Struktur menu sesuai flow rental: Pelanggan → Verifikasi Dokumen → Booking/Sewa →
// Serah Terima → Pengembalian → Pemeriksaan → Deposit/Denda → Selesai.
// Semua rute di bawah masih berupa placeholder "sedang dikembangkan" (ComingSoonController)
// kecuali dashboard (DashboardController) — akan dibangun satu per satu.
$sewaRoutes = function () {
    $stub = Sewa\ComingSoonController::class;

    // Pelanggan
    Route::get('{outlet}/customers',               [Sewa\CustomerController::class, 'index'])->name('customers.index');
    Route::get('{outlet}/customers/create',        [Sewa\CustomerController::class, 'create'])->name('customers.create');
    Route::post('{outlet}/customers',              [Sewa\CustomerController::class, 'store'])->name('customers.store');
    Route::post('{outlet}/customers/quick',        [Sewa\CustomerController::class, 'quickStore'])->name('customers.quick-store');
    Route::get('{outlet}/customers/{customer}',    [Sewa\CustomerController::class, 'show'])->name('customers.show');
    Route::patch('{outlet}/customers/{customer}',  [Sewa\CustomerController::class, 'update'])->name('customers.update');
    Route::delete('{outlet}/customers/{customer}', [Sewa\CustomerController::class, 'destroy'])->name('customers.destroy');

    Route::post('{outlet}/customers/{customer}/documents',                    [Sewa\DocumentController::class, 'upload'])->name('customers.documents.upload');
    Route::patch('{outlet}/customers/{customer}/documents/{document}/verify', [Sewa\DocumentController::class, 'verify'])->name('customers.documents.verify');
    Route::patch('{outlet}/customers/{customer}/documents/{document}/reject', [Sewa\DocumentController::class, 'reject'])->name('customers.documents.reject');
    Route::delete('{outlet}/customers/{customer}/documents/{document}',       [Sewa\DocumentController::class, 'destroy'])->name('customers.documents.destroy');

    Route::get('{outlet}/documents', [Sewa\DocumentController::class, 'index'])->name('documents.index');

    // Booking
    Route::get('{outlet}/bookings/create', [$stub, 'show'])->name('bookings.create');
    Route::get('{outlet}/bookings/active', [$stub, 'show'])->name('bookings.active');
    Route::get('{outlet}/calendar',        [$stub, 'show'])->name('calendar.index');
    Route::get('{outlet}/bookings/done',   [$stub, 'show'])->name('bookings.done');

    // Sewa
    Route::get('{outlet}/rentals/create',  [Sewa\RentalController::class, 'create'])->name('rentals.create');
    Route::post('{outlet}/rentals',        [Sewa\RentalController::class, 'store'])->name('rentals.store');
    Route::get('{outlet}/rentals/active',  [Sewa\RentalController::class, 'active'])->name('rentals.active');
    Route::get('{outlet}/rentals/extend',  [Sewa\RentalController::class, 'extend'])->name('rentals.extend');
    Route::post('{outlet}/rentals/{rental}/extend', [Sewa\RentalController::class, 'storeExtension'])->name('rentals.extend.store');
    Route::get('{outlet}/rentals/returns', [Sewa\RentalController::class, 'returns'])->name('rentals.returns');
    Route::post('{outlet}/rentals/{rental}/return', [Sewa\RentalController::class, 'processReturn'])->name('rentals.returns.process');
    Route::post('{outlet}/rentals/{rental}/pay', [Sewa\RentalController::class, 'storePayment'])->name('rentals.pay');
    Route::get('{outlet}/rentals/{rental}/receipt', [Sewa\RentalController::class, 'receipt'])->name('rentals.receipt');
    Route::get('{outlet}/rentals/{rental}/edit', [Sewa\RentalController::class, 'edit'])->name('rentals.edit');
    Route::put('{outlet}/rentals/{rental}', [Sewa\RentalController::class, 'updateRental'])->name('rentals.update');
    Route::get('{outlet}/rentals/{rental}', [Sewa\RentalController::class, 'show'])->name('rentals.show');

    // Barang
    Route::get('{outlet}/items',                    [Sewa\ItemController::class, 'index'])->name('items.index');
    Route::post('{outlet}/items',                   [Sewa\ItemController::class, 'store'])->name('items.store');
    Route::put('{outlet}/items/{item}',              [Sewa\ItemController::class, 'update'])->name('items.update');
    Route::delete('{outlet}/items/{item}',           [Sewa\ItemController::class, 'destroy'])->name('items.destroy');
    Route::post('{outlet}/items/{item}/toggle-active',[Sewa\ItemController::class, 'toggleActive'])->name('items.toggle-active');
    Route::get('{outlet}/units',            [Sewa\UnitController::class, 'index'])->name('units.index');
    Route::post('{outlet}/units',           [Sewa\UnitController::class, 'store'])->name('units.store');
    Route::put('{outlet}/units/{unit}',     [Sewa\UnitController::class, 'update'])->name('units.update');
    Route::delete('{outlet}/units/{unit}',  [Sewa\UnitController::class, 'destroy'])->name('units.destroy');
    Route::get('{outlet}/availability',                 [Sewa\AvailabilityController::class, 'index'])->name('availability.index');
    Route::patch('{outlet}/availability/{unit}/status', [Sewa\AvailabilityController::class, 'updateStatus'])->name('availability.update-status');

    Route::get('{outlet}/maintenance',                       [Sewa\MaintenanceController::class, 'index'])->name('maintenance.index');
    Route::post('{outlet}/maintenance',                      [Sewa\MaintenanceController::class, 'store'])->name('maintenance.store');
    Route::put('{outlet}/maintenance/{maintenance}',         [Sewa\MaintenanceController::class, 'update'])->name('maintenance.update');
    Route::patch('{outlet}/maintenance/{maintenance}/finish',[Sewa\MaintenanceController::class, 'finish'])->name('maintenance.finish');
    Route::delete('{outlet}/maintenance/{maintenance}',      [Sewa\MaintenanceController::class, 'destroy'])->name('maintenance.destroy');

    // Transaksi
    Route::get('{outlet}/payments', [Sewa\PaymentController::class, 'index'])->name('payments.index');
    Route::get('{outlet}/payments/{payment}', [Sewa\PaymentController::class, 'show'])->name('payments.show');
    Route::get('{outlet}/deposits', [Sewa\DepositController::class, 'index'])->name('deposits.index');
    Route::get('{outlet}/fines',    [Sewa\FineController::class, 'index'])->name('fines.index');
    Route::post('{outlet}/fines/{rental}/pay', [Sewa\FineController::class, 'pay'])->name('fines.pay');
    Route::get('{outlet}/refunds',  [Sewa\RefundController::class, 'index'])->name('refunds.index');
    Route::post('{outlet}/refunds/{rental}/mark-refunded', [Sewa\RefundController::class, 'markRefunded'])->name('refunds.mark-refunded');

    // Pengeluaran
    Route::get('{outlet}/expenses',                     [Sewa\ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('{outlet}/expenses',                    [Sewa\ExpenseController::class, 'store'])->name('expenses.store');
    Route::patch('{outlet}/expenses/{expense}',         [Sewa\ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('{outlet}/expenses/{expense}',        [Sewa\ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('{outlet}/expense-categories',          [Sewa\ExpenseController::class, 'storeCategory'])->name('expense-categories.store');
    Route::delete('{outlet}/expense-categories/{slug}', [Sewa\ExpenseController::class, 'destroyCategory'])->name('expense-categories.destroy');

    // Laporan
    Route::get('{outlet}/reports/revenue',  [Sewa\RevenueReportController::class, 'index'])->name('reports.revenue');
    Route::get('{outlet}/reports/rentals',  [Sewa\RentalReportController::class, 'index'])->name('reports.rentals');
    Route::get('{outlet}/reports/items',    [Sewa\ItemReportController::class, 'index'])->name('reports.items');
    Route::get('{outlet}/reports/fines',    [Sewa\FineReportController::class, 'index'])->name('reports.fines');
    Route::get('{outlet}/reports/deposits', [Sewa\DepositReportController::class, 'index'])->name('reports.deposits');
    Route::get('{outlet}/reports/profit-loss', [Sewa\ProfitLossController::class, 'index'])->name('reports.profit-loss');

    // Lainnya
    Route::get('{outlet}/settings',  [Sewa\SettingController::class, 'edit'])->name('settings.edit');
    Route::patch('{outlet}/settings',[Sewa\SettingController::class, 'update'])->name('settings.update');

    Route::post('{outlet}/settings/requirements',                [Sewa\SettingController::class, 'storeRequirement'])->name('settings.requirements.store');
    Route::patch('{outlet}/settings/requirements/{requirement}', [Sewa\SettingController::class, 'updateRequirement'])->name('settings.requirements.update');
    Route::delete('{outlet}/settings/requirements/{requirement}',[Sewa\SettingController::class, 'destroyRequirement'])->name('settings.requirements.destroy');
    Route::patch('{outlet}/settings/payment', [Sewa\SettingController::class, 'updatePayment'])->name('settings.payment.update');
    Route::patch('{outlet}/settings/receipt', [Sewa\SettingController::class, 'updateReceipt'])->name('settings.receipt.update');
};

// ── Retail — toko kelontong, pakaian, elektronik (prefix: retail/)
// Manajemen harga jual (HPP). Tanpa orders/kitchen, tanpa opening/closing stok.
$retailRoutes = function () {
    Route::get('{outlet}/settings',  [Retail\SettingController::class, 'edit'])->name('settings.edit');
    Route::patch('{outlet}/settings',[Retail\SettingController::class, 'update'])->name('settings.update');

    Route::get('{outlet}/shifts',                        [Retail\ShiftController::class, 'index'])->name('shifts.index');
    Route::post('{outlet}/shifts',                       [Retail\ShiftController::class, 'store'])->name('shifts.store');
    Route::patch('{outlet}/shifts/{shift}/close',        [Retail\ShiftController::class, 'close'])->name('shifts.close');
    Route::patch('{outlet}/shifts/{shift}',              [Retail\ShiftController::class, 'update'])->name('shifts.update');
    Route::delete('{outlet}/shifts/{shift}',             [Retail\ShiftController::class, 'destroy'])->name('shifts.destroy');

    Route::get('{outlet}/categories', [Retail\CategoryController::class, 'index'])->name('categories.index');

    Route::get('{outlet}/products',                              [Retail\ProductController::class, 'index'])->name('products.index');
    Route::post('{outlet}/products',                             [Retail\ProductController::class, 'store'])->name('products.store');
    Route::match(['put','patch'],'{outlet}/products/{product}',  [Retail\ProductController::class, 'update'])->name('products.update');
    Route::delete('{outlet}/products/{product}',                 [Retail\ProductController::class, 'destroy'])->name('products.destroy');
    Route::post('{outlet}/products/{product}/toggle-active',     [Retail\ProductController::class, 'toggleActive'])->name('products.toggle-active');
    // Manajemen harga jual — khusus retail
    Route::get('{outlet}/products/{product}/harga',   [Retail\PriceController::class, 'edit'])->name('products.price.edit');
    Route::patch('{outlet}/products/{product}/harga', [Retail\PriceController::class, 'update'])->name('products.price.update');

    Route::get('{outlet}/pos',  [Retail\PosController::class, 'index'])->name('pos.index');
    Route::post('{outlet}/pos', [Retail\PosController::class, 'store'])->name('pos.store');

    Route::get('{outlet}/transactions',                       [Retail\TransactionController::class, 'index'])->name('transactions.index');
    Route::get('{outlet}/transactions/{transaction}',         [Retail\TransactionController::class, 'show'])->name('transactions.show');
    Route::get('{outlet}/transactions/{transaction}/receipt', [Retail\TransactionController::class, 'receipt'])->name('transactions.receipt');
    Route::patch('{outlet}/transactions/{transaction}/void',  [Retail\TransactionController::class, 'void'])->name('transactions.void');

    Route::get('{outlet}/stock', [Retail\StockViewController::class, 'index'])->name('stock.index');

    Route::get('{outlet}/expenses',                     [Retail\ExpenseController::class, 'index'])->name('expenses.index');
    Route::post('{outlet}/expenses',                    [Retail\ExpenseController::class, 'store'])->name('expenses.store');
    Route::patch('{outlet}/expenses/{expense}',         [Retail\ExpenseController::class, 'update'])->name('expenses.update');
    Route::delete('{outlet}/expenses/{expense}',        [Retail\ExpenseController::class, 'destroy'])->name('expenses.destroy');
    Route::post('{outlet}/expense-categories',          [Retail\ExpenseController::class, 'storeCategory'])->name('expense-categories.store');
    Route::delete('{outlet}/expense-categories/{slug}', [Retail\ExpenseController::class, 'destroyCategory'])->name('expense-categories.destroy');

    Route::get('{outlet}/stock-in',                [Retail\StockInController::class, 'index'])->name('stock-in.index');
    Route::get('{outlet}/stock-in/create',         [Retail\StockInController::class, 'create'])->name('stock-in.create');
    Route::post('{outlet}/stock-in',               [Retail\StockInController::class, 'store'])->name('stock-in.store');
    Route::get('{outlet}/stock-in/{stockIn}',      [Retail\StockInController::class, 'show'])->name('stock-in.show');
    Route::get('{outlet}/stock-in/{stockIn}/edit', [Retail\StockInController::class, 'edit'])->name('stock-in.edit');
    Route::patch('{outlet}/stock-in/{stockIn}',    [Retail\StockInController::class, 'update'])->name('stock-in.update');
    Route::delete('{outlet}/stock-in/{stockIn}',   [Retail\StockInController::class, 'destroy'])->name('stock-in.destroy');

    Route::get('{outlet}/waste',            [Retail\WasteController::class, 'index'])->name('waste.index');
    Route::get('{outlet}/waste/create',     [Retail\WasteController::class, 'create'])->name('waste.create');
    Route::post('{outlet}/waste',           [Retail\WasteController::class, 'store'])->name('waste.store');
    Route::get('{outlet}/waste/{waste}',    [Retail\WasteController::class, 'show'])->name('waste.show');
    Route::delete('{outlet}/waste/{waste}', [Retail\WasteController::class, 'destroy'])->name('waste.destroy');

    Route::get('{outlet}/reports/sales',        [Retail\SalesReportController::class, 'index'])->name('reports.sales');
    Route::get('{outlet}/reports/sales/export', [Retail\SalesReportController::class, 'export'])->name('reports.sales.export');
    Route::get('{outlet}/reports/profit-loss',  [Retail\ProfitLossController::class, 'index'])->name('reports.profit-loss');

    Route::get('{outlet}/self-orders',                  [\App\Http\Controllers\SelfOrderController::class, 'index'])->name('self-orders.index');
    Route::get('{outlet}/self-orders/{order}',          [\App\Http\Controllers\SelfOrderController::class, 'show'])->name('self-orders.show');
    Route::post('{outlet}/self-orders/{order}/approve', [\App\Http\Controllers\SelfOrderController::class, 'approve'])->name('self-orders.approve');
    Route::patch('{outlet}/self-orders/{order}/reject', [\App\Http\Controllers\SelfOrderController::class, 'reject'])->name('self-orders.reject');
    Route::delete('{outlet}/self-orders/{order}',       [\App\Http\Controllers\SelfOrderController::class, 'destroy'])->name('self-orders.destroy');
};

// Halaman publik (tanpa login) — cek status pesanan laundry via QR
Route::get('/cek-pesanan/{token}', [\App\Http\Controllers\Laundry\TrackOrderController::class, 'show'])
    ->middleware('dynamic.throttle:public_lookup')
    ->name('laundry.track');

// Rute utama
Route::middleware(['auth', 'verified', 'require.setup'])->group(function () use ($fnbRoutes, $salonRoutes, $laundryRoutes, $retailRoutes, $sewaRoutes) {
    Route::get('/dashboard', function () {
        /** @var \App\Models\User $user */
        $user            = Auth::user();
        $role            = $user->role;
        $showWelcomeTour = session()->pull('pabalu_welcome_tour', false);

        /* ── ADMIN ── */
        if ($role === 'admin') {
            $owners = User::whereHas('roleRelation', fn ($q) => $q->where('slug', 'owner'))
                ->withCount('outlets')
                ->orderBy('name')
                ->get();

            $ownerCount  = $owners->count();
            $outletCount = Outlet::count();

            $txToday  = Transaction::whereDate('date', today())->where('status', 'completed')->count();
            $revToday = Transaction::whereDate('date', today())->where('status', 'completed')->sum('total');
            $txMonth  = Transaction::whereYear('date', now()->year)->whereMonth('date', now()->month)->where('status', 'completed')->count();
            $revMonth = Transaction::whereYear('date', now()->year)->whereMonth('date', now()->month)->where('status', 'completed')->sum('total');

            $weeklyStats = Transaction::where('status', 'completed')
                ->whereDate('date', '>=', now()->subDays(6)->toDateString())
                ->selectRaw('DATE(date) as day, COUNT(*) as trx_count, SUM(total) as revenue')
                ->groupBy('day')->orderBy('day')->get()->keyBy('day');

            $recentTransactions = Transaction::with(['outlet', 'user'])
                ->where('status', 'completed')
                ->orderByDesc('created_at')->limit(8)->get();

            return view('dashboard', compact(
                'user', 'role', 'showWelcomeTour',
                'owners', 'ownerCount', 'outletCount',
                'txToday', 'revToday', 'txMonth', 'revMonth',
                'weeklyStats', 'recentTransactions'
            ));
        }

        /* ── OWNER ── */
        if ($role === 'owner') {
            $outlets   = $user->outlets()->with('outletType')->get();
            $outletIds = $outlets->pluck('id');

            $employeeCount = Employee::where('owner_id', $user->id)->where('is_active', true)->count();

            $txToday  = Transaction::whereIn('outlet_id', $outletIds)->whereDate('date', today())->where('status', 'completed')->count();
            $revToday = Transaction::whereIn('outlet_id', $outletIds)->whereDate('date', today())->where('status', 'completed')->sum('total');
            $txMonth  = Transaction::whereIn('outlet_id', $outletIds)->whereYear('date', now()->year)->whereMonth('date', now()->month)->where('status', 'completed')->count();
            $revMonth = Transaction::whereIn('outlet_id', $outletIds)->whereYear('date', now()->year)->whereMonth('date', now()->month)->where('status', 'completed')->sum('total');

            $weeklyStats = Transaction::whereIn('outlet_id', $outletIds)
                ->where('status', 'completed')
                ->whereDate('date', '>=', now()->subDays(6)->toDateString())
                ->selectRaw('DATE(date) as day, COUNT(*) as trx_count, SUM(total) as revenue')
                ->groupBy('day')->orderBy('day')->get()->keyBy('day');

            $outletStats = $outletIds->isNotEmpty()
                ? Transaction::whereIn('outlet_id', $outletIds)->whereDate('date', today())->where('status', 'completed')
                    ->selectRaw('outlet_id, COUNT(*) as tx_count, SUM(total) as revenue')
                    ->groupBy('outlet_id')->get()->keyBy('outlet_id')
                : collect();

            $recentTransactions = $outletIds->isNotEmpty()
                ? Transaction::whereIn('outlet_id', $outletIds)->with(['outlet', 'user'])
                    ->where('status', 'completed')->orderByDesc('created_at')->limit(8)->get()
                : collect();

            $unpaidInvoices     = $outletIds->isNotEmpty()
                ? ProOwnerInvoice::whereIn('outlet_id', $outletIds)->where('status', 'belum_lunas')->with('outlet')->get()
                : collect();
            $unpaidInvoiceCount = $unpaidInvoices->count();
            $unpaidInvoiceTotal = $unpaidInvoices->sum('amount');

            return view('dashboard', compact(
                'user', 'role', 'showWelcomeTour',
                'outlets', 'employeeCount',
                'txToday', 'revToday', 'txMonth', 'revMonth',
                'weeklyStats', 'outletStats', 'recentTransactions',
                'unpaidInvoices', 'unpaidInvoiceCount', 'unpaidInvoiceTotal'
            ));
        }

        /* ── KASIR / ADMIN OUTLET ── */
        $assignedOutlets = $user->assignedOutlets()->with('outletType')->where('is_active', true)->get();
        $outletIds       = $assignedOutlets->pluck('id');

        $txQuery = Transaction::whereIn('outlet_id', $outletIds)->where('status', 'completed')->whereDate('date', today());
        if ($role === 'kasir') {
            $txQuery->where('user_id', $user->id);
        }
        $txToday  = (clone $txQuery)->count();
        $revToday = (clone $txQuery)->sum('total');

        return view('dashboard', compact(
            'user', 'role', 'showWelcomeTour',
            'assignedOutlets', 'txToday', 'revToday'
        ));
    })->name('dashboard');

    Route::get('/my-salary', [EmployeeController::class, 'mySalary'])->name('my-salary');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/password', [ProfileController::class, 'editPassword'])->name('profile.password');

    // Kelola User
    Route::resource('users', UserController::class);
    Route::patch('users/{user}/password', [UserController::class, 'updatePassword'])->name('users.password');
    Route::post('users/{user}/verify', [UserController::class, 'verify'])->name('users.verify');
    Route::post('users/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('users.toggle-active');

    // Kelola Karyawan
    Route::get('employees',                                       [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('employees',                                      [EmployeeController::class, 'store'])->name('employees.store');
    Route::get('employees/{employee}',                            [EmployeeController::class, 'show'])->name('employees.show');
    Route::put('employees/{employee}',                            [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('employees/{employee}',                         [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::post('employees/{employee}/toggle-active',             [EmployeeController::class, 'toggleActive'])->name('employees.toggle-active');
    Route::post('employees/{employee}/salaries',                  [EmployeeController::class, 'storeSalary'])->name('employees.salaries.store');
    Route::post('employees/{employee}/salaries/{salary}/activate',[EmployeeController::class, 'activateSalary'])->name('employees.salaries.activate');
    Route::delete('employees/{employee}/salaries/{salary}',       [EmployeeController::class, 'destroySalary'])->name('employees.salaries.destroy');
    Route::get('employees/{employee}/salary-payments/create',     [EmployeeController::class, 'createSalaryPayment'])->name('employees.salary-payments.create');
    Route::post('employees/{employee}/salary-payments',           [EmployeeController::class, 'storeSalaryPayment'])->name('employees.salary-payments.store');
    Route::delete('employees/{employee}/salary-payments/{payment}', [EmployeeController::class, 'destroySalaryPayment'])->name('employees.salary-payments.destroy');
    Route::post('employees/{employee}/account',                   [EmployeeController::class, 'createAccount'])->name('employees.account.create');
    Route::put('employees/{employee}/account',                    [EmployeeController::class, 'updateAccount'])->name('employees.account.update');

    // Jenis Outlet
    Route::resource('outlet-types', OutletTypeController::class)->except(['create', 'edit']);
    Route::post('outlet-types/{outlet_type}/toggle-active', [OutletTypeController::class, 'toggleActive'])->name('outlet-types.toggle-active');

    // Kategori per Jenis Outlet (dikelola admin, dipakai bersama oleh semua outlet jenis ini)
    Route::post('outlet-types/{outlet_type}/categories',                              [OutletTypeCategoryController::class, 'store'])->name('outlet-types.categories.store');
    Route::match(['put', 'patch'], 'outlet-types/{outlet_type}/categories/{category}', [OutletTypeCategoryController::class, 'update'])->name('outlet-types.categories.update');
    Route::delete('outlet-types/{outlet_type}/categories/{category}',                  [OutletTypeCategoryController::class, 'destroy'])->name('outlet-types.categories.destroy');
    Route::post('outlet-types/{outlet_type}/categories/{category}/toggle-active',      [OutletTypeCategoryController::class, 'toggleActive'])->name('outlet-types.categories.toggle-active');

    // Mitra UMKM (logo kerjasama)
    Route::resource('partners', PartnerController::class)->except(['create', 'edit', 'show']);
    Route::post('partners/{partner}/toggle-active', [PartnerController::class, 'toggleActive'])->name('partners.toggle-active');

    // Halaman (CMS pages statis — Tentang Kami, Kebijakan Privasi, dll.)
    Route::post('pages/upload-image', [\App\Http\Controllers\PageController::class, 'uploadImage'])->name('pages.upload-image');
    Route::resource('pages', \App\Http\Controllers\PageController::class)->except(['show']);
    Route::post('pages/{page}/toggle-active', [\App\Http\Controllers\PageController::class, 'toggleActive'])->name('pages.toggle-active');

    // Pengaturan Homepage (admin only) — tab Header (slider), Menu, Kategori & Iklan/Promo
    Route::prefix('pengaturan')->name('settings.')->group(function () {
        Route::get('/', [\App\Http\Controllers\HomepageSettingController::class, 'index'])->name('index');
        Route::get('header', [\App\Http\Controllers\HomepageSettingController::class, 'header'])->name('header');
        Route::get('menu', [\App\Http\Controllers\HomepageSettingController::class, 'menu'])->name('menu');
        Route::get('kategori', [\App\Http\Controllers\HomepageSettingController::class, 'categories'])->name('categories');
        Route::get('iklan', [\App\Http\Controllers\HomepageSettingController::class, 'promo'])->name('promo');
    });
    Route::prefix('pengaturan/header/sliders')->name('sliders.')->group(function () {
        Route::post('/', [\App\Http\Controllers\HomeSliderController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '{slider}', [\App\Http\Controllers\HomeSliderController::class, 'update'])->name('update');
        Route::delete('{slider}', [\App\Http\Controllers\HomeSliderController::class, 'destroy'])->name('destroy');
        Route::post('{slider}/toggle-active', [\App\Http\Controllers\HomeSliderController::class, 'toggleActive'])->name('toggle-active');
    });
    Route::prefix('pengaturan/iklan/banners')->name('promo-banners.')->group(function () {
        Route::post('/', [\App\Http\Controllers\PromoBannerController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '{promoBanner}', [\App\Http\Controllers\PromoBannerController::class, 'update'])->name('update');
        Route::delete('{promoBanner}', [\App\Http\Controllers\PromoBannerController::class, 'destroy'])->name('destroy');
        Route::post('{promoBanner}/toggle-active', [\App\Http\Controllers\PromoBannerController::class, 'toggleActive'])->name('toggle-active');
    });
    Route::prefix('pengaturan/menu/items')->name('home-menus.')->group(function () {
        Route::post('/', [\App\Http\Controllers\HomeMenuController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '{homeMenu}', [\App\Http\Controllers\HomeMenuController::class, 'update'])->name('update');
        Route::delete('{homeMenu}', [\App\Http\Controllers\HomeMenuController::class, 'destroy'])->name('destroy');
        Route::post('{homeMenu}/toggle-active', [\App\Http\Controllers\HomeMenuController::class, 'toggleActive'])->name('toggle-active');
    });
    Route::prefix('pengaturan/kategori/items')->name('home-categories.')->group(function () {
        Route::post('/', [\App\Http\Controllers\HomeCategoryController::class, 'store'])->name('store');
        Route::match(['put', 'patch'], '{homeCategory}', [\App\Http\Controllers\HomeCategoryController::class, 'update'])->name('update');
        Route::delete('{homeCategory}', [\App\Http\Controllers\HomeCategoryController::class, 'destroy'])->name('destroy');
        Route::post('{homeCategory}/toggle-active', [\App\Http\Controllers\HomeCategoryController::class, 'toggleActive'])->name('toggle-active');
    });

    // Kelola Paket Pro (admin only)
    Route::prefix('admin/pro')->name('admin.pro.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\ProAdminController::class, 'features'])->name('index');
        Route::get('fitur', [\App\Http\Controllers\Admin\ProAdminController::class, 'features'])->name('features');

        Route::get('paket', [\App\Http\Controllers\Admin\ProAdminController::class, 'plans'])->name('plans');
        Route::post('paket', [\App\Http\Controllers\Admin\ProAdminController::class, 'storePlan'])->name('plans.store');
        Route::put('paket/{plan}', [\App\Http\Controllers\Admin\ProAdminController::class, 'updatePlan'])->name('plans.update');
        Route::post('paket/{plan}/toggle-active', [\App\Http\Controllers\Admin\ProAdminController::class, 'togglePlanActive'])->name('plans.toggle-active');
        Route::delete('paket/{plan}', [\App\Http\Controllers\Admin\ProAdminController::class, 'destroyPlan'])->name('plans.destroy');

        Route::get('kode', [\App\Http\Controllers\Admin\ProAdminController::class, 'codes'])->name('codes');
        Route::post('kode', [\App\Http\Controllers\Admin\ProAdminController::class, 'storeCode'])->name('codes.store');
        Route::get('kode/{code}', [\App\Http\Controllers\Admin\ProAdminController::class, 'showCode'])->name('codes.show');
        Route::put('kode/{code}', [\App\Http\Controllers\Admin\ProAdminController::class, 'updateCode'])->name('codes.update');
        Route::delete('kode/{code}', [\App\Http\Controllers\Admin\ProAdminController::class, 'destroyCode'])->name('codes.destroy');
        Route::delete('kode/{code}/pemakaian/{subscription}', [\App\Http\Controllers\Admin\ProAdminController::class, 'destroyUsage'])->name('codes.usage.destroy');
    });

    // Tagihan Pro Plan (admin only) — menu tersendiri, terpisah dari Kelola Paket Pro
    // Rute literal (kode/, invoice/) DIDAFTAR DULU sebelum {outlet} wildcard di bawah,
    // supaya tidak "tertangkap" duluan oleh route-model-binding {outlet}.
    Route::prefix('admin/tagihan')->name('admin.tagihan.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\OutletBillingController::class, 'index'])->name('index');

        Route::get('kode', [\App\Http\Controllers\Admin\OutletBillingController::class, 'paymentCodes'])->name('codes');
        Route::post('kode', [\App\Http\Controllers\Admin\OutletBillingController::class, 'storePaymentCode'])->name('codes.store');
        Route::get('kode/{paymentCode}', [\App\Http\Controllers\Admin\OutletBillingController::class, 'showPaymentCode'])->name('codes.show');
        Route::delete('kode/{paymentCode}', [\App\Http\Controllers\Admin\OutletBillingController::class, 'destroyPaymentCode'])->name('codes.destroy');

        Route::get('pendapatan/{type}', [\App\Http\Controllers\Admin\OutletBillingController::class, 'revenueDetail'])
            ->where('type', 'dibayar|gratis')->name('revenue');

        Route::patch('invoice/{invoice}/lunas', [\App\Http\Controllers\Admin\OutletBillingController::class, 'markInvoicePaid'])->name('invoices.mark-paid');
        Route::delete('invoice/{invoice}', [\App\Http\Controllers\Admin\OutletBillingController::class, 'destroyInvoice'])->name('invoices.destroy');

        Route::get('{outlet}', [\App\Http\Controllers\Admin\OutletBillingController::class, 'show'])->name('show');
        Route::get('{outlet}/hitung', [\App\Http\Controllers\Admin\OutletBillingController::class, 'calcTransaction'])->name('calc');
        Route::post('{outlet}', [\App\Http\Controllers\Admin\OutletBillingController::class, 'storeInvoice'])->name('store');
    });

    // Langganan Pro (owner)
    Route::get('langganan', [\App\Http\Controllers\ProSubscriptionController::class, 'show'])->name('pro.subscription');
    Route::post('langganan/aktivasi', [\App\Http\Controllers\ProSubscriptionController::class, 'redeem'])->name('pro.subscription.redeem');
    Route::post('langganan/aktifkan/{plan}', [\App\Http\Controllers\ProSubscriptionController::class, 'activate'])->name('pro.subscription.activate');

    // Pengaturan Sistem (admin only) — zona waktu aplikasi, gateway WhatsApp (OTP)
    Route::get('pengaturan-sistem', [\App\Http\Controllers\SystemSettingController::class, 'general'])->name('system-settings.edit');
    Route::put('pengaturan-sistem', [\App\Http\Controllers\SystemSettingController::class, 'update'])->name('system-settings.update');
    Route::get('pengaturan-sistem/whatsapp', [\App\Http\Controllers\SystemSettingController::class, 'whatsapp'])->name('system-settings.whatsapp');
    Route::put('pengaturan-sistem/whatsapp', [\App\Http\Controllers\SystemSettingController::class, 'updateWhatsapp'])->name('system-settings.whatsapp.update');
    Route::post('pengaturan-sistem/whatsapp/test', [\App\Http\Controllers\SystemSettingController::class, 'testWhatsapp'])->name('system-settings.whatsapp.test');
    Route::get('pengaturan-sistem/rate-limit', [\App\Http\Controllers\SystemSettingController::class, 'rateLimits'])->name('system-settings.rate-limit');
    Route::put('pengaturan-sistem/rate-limit', [\App\Http\Controllers\SystemSettingController::class, 'updateRateLimits'])->name('system-settings.rate-limit.update');

    // Wilayah Indonesia (admin only)
    Route::prefix('wilayah')->name('wilayah.')->group(function () {
        Route::get('/', [\App\Http\Controllers\WilayahController::class, 'index'])->name('index');
        // Provinces
        Route::post('/provinces', [\App\Http\Controllers\WilayahController::class, 'storeProvince'])->name('provinces.store');
        Route::patch('/provinces/{province}', [\App\Http\Controllers\WilayahController::class, 'updateProvince'])->name('provinces.update');
        Route::delete('/provinces/{province}', [\App\Http\Controllers\WilayahController::class, 'destroyProvince'])->name('provinces.destroy');
        // Province → Regencies
        Route::get('/provinces/{province}', [\App\Http\Controllers\WilayahController::class, 'showProvince'])->name('provinces.show');
        Route::post('/provinces/{province}/regencies', [\App\Http\Controllers\WilayahController::class, 'storeRegency'])->name('provinces.regencies.store');
        Route::patch('/provinces/{province}/regencies/{regency}', [\App\Http\Controllers\WilayahController::class, 'updateRegency'])->name('provinces.regencies.update');
        Route::delete('/provinces/{province}/regencies/{regency}', [\App\Http\Controllers\WilayahController::class, 'destroyRegency'])->name('provinces.regencies.destroy');
        // Regency → Districts
        Route::get('/provinces/{province}/regencies/{regency}', [\App\Http\Controllers\WilayahController::class, 'showRegency'])->name('provinces.regencies.show');
        Route::post('/provinces/{province}/regencies/{regency}/districts', [\App\Http\Controllers\WilayahController::class, 'storeDistrict'])->name('provinces.regencies.districts.store');
        Route::patch('/provinces/{province}/regencies/{regency}/districts/{district}', [\App\Http\Controllers\WilayahController::class, 'updateDistrict'])->name('provinces.regencies.districts.update');
        Route::delete('/provinces/{province}/regencies/{regency}/districts/{district}', [\App\Http\Controllers\WilayahController::class, 'destroyDistrict'])->name('provinces.regencies.districts.destroy');
        // API cascading (no auth required for dropdown use)
        Route::get('/api/regencies', [\App\Http\Controllers\WilayahController::class, 'apiRegencies'])->name('api.regencies');
        Route::get('/api/districts', [\App\Http\Controllers\WilayahController::class, 'apiDistricts'])->name('api.districts');
    });

    // Kelola Outlet (admin-level CRUD)
    Route::resource('outlets', OutletController::class)->except(['edit', 'show']);
    Route::post('outlets/{outlet}/toggle-active', [OutletController::class, 'toggleActive'])->name('outlets.toggle-active');

    // Tagihan outlet (owner/admin_outlet) — lihat tagihan yang diinput admin & lunasi via kode
    Route::prefix('outlet/{outlet}/tagihan')->name('outlet.tagihan.')->group(function () {
        Route::get('/', [\App\Http\Controllers\OutletInvoiceController::class, 'index'])->name('index');
        Route::post('{invoice}/lunas-kode', [\App\Http\Controllers\OutletInvoiceController::class, 'redeem'])->name('redeem');
    });

    // Role & Permission
    Route::resource('roles', RoleController::class);

    // ── F&B: warung makan, kafe (prefix: fnb/)
    Route::prefix('fnb')->name('fnb.')->group(function () use ($fnbRoutes) {
        Route::get('{outlet}', [Fnb\DashboardController::class, 'show'])->name('show');
        $fnbRoutes();
    });

    // ── Salon: jasa kecantikan (prefix: salon/)
    Route::prefix('salon')->name('salon.')->group(function () use ($salonRoutes) {
        Route::get('{outlet}', [Salon\DashboardController::class, 'show'])->name('show');
        $salonRoutes();
    });

    // ── Laundry: jasa laundry (prefix: laundry/)
    Route::prefix('laundry')->name('laundry.')->group(function () use ($laundryRoutes) {
        Route::get('{outlet}', [Laundry\DashboardController::class, 'show'])->name('show');
        $laundryRoutes();
    });

    // ── Retail: kelontong, pakaian, elektronik (prefix: retail/)
    Route::prefix('retail')->name('retail.')->group(function () use ($retailRoutes) {
        Route::get('{outlet}', [Retail\DashboardController::class, 'show'])->name('show');
        $retailRoutes();
    });

    // ── Sewa: jasa rental (prefix: sewa/)
    Route::prefix('sewa')->name('sewa.')->group(function () use ($sewaRoutes) {
        Route::get('{outlet}', [Sewa\DashboardController::class, 'show'])->name('show');
        $sewaRoutes();
    });

    // Daftar & profil owner (admin only)
    Route::get('/owners', [\App\Http\Controllers\OwnerProfileController::class, 'index'])->name('owners.index');
    Route::get('/owners/{user}', [\App\Http\Controllers\OwnerProfileController::class, 'show'])->name('owners.show');
    Route::patch('/owners/{user}/outlet-quota', [\App\Http\Controllers\OwnerProfileController::class, 'updateOutletQuota'])->name('owners.outlet-quota.update');

    // Chat (owner ↔ admin)
    Route::prefix('chat')->name('chat.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ChatController::class, 'index'])->name('index');
        Route::get('conversations', [\App\Http\Controllers\ChatController::class, 'conversations'])->name('conversations');
        Route::get('messages/{ownerId}', [\App\Http\Controllers\ChatController::class, 'messages'])->name('messages');
        Route::post('send', [\App\Http\Controllers\ChatController::class, 'send'])->name('send');
        Route::get('unread-count', [\App\Http\Controllers\ChatController::class, 'unreadCount'])->name('unread-count');
    });

    // Laporan Sistem (owner kirim, admin kelola)
    Route::post('system-reports', [\App\Http\Controllers\SystemReportController::class, 'store'])->name('system-reports.store');
    Route::get('system-reports/mine', [\App\Http\Controllers\SystemReportController::class, 'mine'])->name('system-reports.mine');
    Route::prefix('system-reports')->name('system-reports.')->group(function () {
        Route::get('/', [\App\Http\Controllers\SystemReportController::class, 'index'])->name('index');
        Route::patch('{report}/read', [\App\Http\Controllers\SystemReportController::class, 'markRead'])->name('read');
        Route::post('{report}/reply', [\App\Http\Controllers\SystemReportController::class, 'reply'])->name('reply');
    });
});

require __DIR__.'/auth.php';
