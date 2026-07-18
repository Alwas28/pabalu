<?php

use App\Http\Controllers\Fnb;
use App\Http\Controllers\Laundry;
use App\Http\Controllers\Retail;
use App\Http\Controllers\Salon;
use App\Http\Controllers\Sewa;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\OutletController;
use App\Http\Controllers\OutletTypeController;
use App\Http\Controllers\PartnerController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SetupController;
use App\Http\Controllers\UserController;
use App\Models\Employee;
use App\Models\Outlet;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// TEMPORARY DIAGNOSTIC — HAPUS SETELAH SELESAI
Route::get('/_diag', function () {
    $users = \App\Models\User::whereHas('roleRelation', fn($q) => $q->where('slug','owner'))
        ->get(['id','name','email','is_active']);
    $outlets = \App\Models\Outlet::with('outletType')->get(['id','name','owner_id','outlet_type_id','is_active']);
    $authUser = \Illuminate\Support\Facades\Auth::user();

    $rows = [];
    foreach ($users as $u) {
        $rows[] = "USER  id={$u->id} | {$u->name} | {$u->email} | active=".($u->is_active?'yes':'no');
    }
    $rows[] = '---';
    foreach ($outlets as $o) {
        $rows[] = "OUTLET id={$o->id} | {$o->name} | owner_id={$o->owner_id} | rp=".$o->rp().' | active='.($o->is_active?'yes':'no');
    }
    $rows[] = '---';
    if ($authUser) {
        $rows[] = "LOGGED_IN id={$authUser->id} | {$authUser->name} | role={$authUser->role}";
        $myOutlets = \App\Models\Outlet::where('owner_id', $authUser->id)->get(['id','name']);
        foreach ($myOutlets as $o) {
            $rows[] = "  MY_OUTLET id={$o->id} | {$o->name}";
        }
    } else {
        $rows[] = 'LOGGED_IN: (tidak login)';
    }
    return response('<pre style="font-family:monospace;padding:20px;font-size:14px">'.implode("\n",$rows).'</pre>');
});
// END TEMPORARY DIAGNOSTIC

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

    return view('welcome', compact('partners', 'outletTypes', 'featuredProducts', 'registeredOutlets'));
});

// ── Public self-ordering F&B (no auth) ─────────────────────────────────────
Route::get('/menu/{code}', [\App\Http\Controllers\Public\MenuController::class, 'index'])->name('public.menu');
Route::post('/menu/{code}/order', [\App\Http\Controllers\Public\MenuController::class, 'placeOrder'])->name('public.menu.order');
Route::get('/menu/{code}/pesanan/{orderNumber}', [\App\Http\Controllers\Public\MenuController::class, 'track'])->name('public.menu.track');

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

    Route::get('{outlet}/categories',                              [Fnb\CategoryController::class, 'index'])->name('categories.index');
    Route::post('{outlet}/categories',                             [Fnb\CategoryController::class, 'store'])->name('categories.store');
    Route::match(['put','patch'],'{outlet}/categories/{category}', [Fnb\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('{outlet}/categories/{category}',                [Fnb\CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('{outlet}/categories/{category}/toggle-active',    [Fnb\CategoryController::class, 'toggleActive'])->name('categories.toggle-active');

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

    Route::get('{outlet}/categories',                              [Salon\CategoryController::class, 'index'])->name('categories.index');
    Route::post('{outlet}/categories',                             [Salon\CategoryController::class, 'store'])->name('categories.store');
    Route::match(['put','patch'],'{outlet}/categories/{category}', [Salon\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('{outlet}/categories/{category}',                [Salon\CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('{outlet}/categories/{category}/toggle-active',    [Salon\CategoryController::class, 'toggleActive'])->name('categories.toggle-active');

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

    Route::get('{outlet}/categories',                              [Laundry\CategoryController::class, 'index'])->name('categories.index');
    Route::post('{outlet}/categories',                             [Laundry\CategoryController::class, 'store'])->name('categories.store');
    Route::match(['put','patch'],'{outlet}/categories/{category}', [Laundry\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('{outlet}/categories/{category}',                [Laundry\CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('{outlet}/categories/{category}/toggle-active',    [Laundry\CategoryController::class, 'toggleActive'])->name('categories.toggle-active');

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

    Route::get('{outlet}/categories',                              [Retail\CategoryController::class, 'index'])->name('categories.index');
    Route::post('{outlet}/categories',                             [Retail\CategoryController::class, 'store'])->name('categories.store');
    Route::match(['put','patch'],'{outlet}/categories/{category}', [Retail\CategoryController::class, 'update'])->name('categories.update');
    Route::delete('{outlet}/categories/{category}',                [Retail\CategoryController::class, 'destroy'])->name('categories.destroy');
    Route::post('{outlet}/categories/{category}/toggle-active',    [Retail\CategoryController::class, 'toggleActive'])->name('categories.toggle-active');

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
Route::get('/cek-pesanan/{orderNumber}', [\App\Http\Controllers\Laundry\TrackOrderController::class, 'show'])
    ->name('laundry.track');

// Rute utama
Route::middleware(['auth', 'verified', 'require.setup'])->group(function () use ($fnbRoutes, $salonRoutes, $laundryRoutes, $retailRoutes) {
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

            return view('dashboard', compact(
                'user', 'role', 'showWelcomeTour',
                'outlets', 'employeeCount',
                'txToday', 'revToday', 'txMonth', 'revMonth',
                'weeklyStats', 'outletStats', 'recentTransactions'
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
    Route::resource('outlet-types', OutletTypeController::class)->except(['create', 'edit', 'show']);
    Route::post('outlet-types/{outlet_type}/toggle-active', [OutletTypeController::class, 'toggleActive'])->name('outlet-types.toggle-active');

    // Mitra UMKM (logo kerjasama)
    Route::resource('partners', PartnerController::class)->except(['create', 'edit', 'show']);
    Route::post('partners/{partner}/toggle-active', [PartnerController::class, 'toggleActive'])->name('partners.toggle-active');

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

    // ── Sewa: jasa sewa (prefix: sewa/) — dashboard saja, fitur lain belum dibuat
    Route::prefix('sewa')->name('sewa.')->group(function () {
        Route::get('{outlet}', [Sewa\DashboardController::class, 'show'])->name('show');
    });

    // Daftar & profil owner (admin only)
    Route::get('/owners', [\App\Http\Controllers\OwnerProfileController::class, 'index'])->name('owners.index');
    Route::get('/owners/{user}', [\App\Http\Controllers\OwnerProfileController::class, 'show'])->name('owners.show');

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
