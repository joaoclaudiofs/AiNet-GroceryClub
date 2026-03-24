<?php

use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

use App\Http\Middleware\Admin;
use App\Http\Middleware\Management;

use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\CardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\MetricsController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\Order2Controller;
use App\Http\Controllers\StockAjustmentController;
use App\Http\Controllers\UserController;



Route::middleware(['auth'])
    ->prefix("stockAdjust")
    ->group(function () {
    Route::get('/sa', [StockAjustmentController::class, 'index'])->name('stockAdjust.index');
    Route::get('/sa/{adjust}', [StockAjustmentController::class, 'show'])->name('stockAdjust.show');
    Route::delete('/delete/{adjust}', [StockAjustmentController::class, 'destroy'])->name('stockAdjust.destroy');
});


Route::middleware(['auth'])
    ->prefix("testing")
    ->group(function () {
    Route::get('/abc', [Order2Controller::class, 'index'])->name('testing.index');
    Route::get('/abc/{order}', [Order2Controller::class, 'show'])->name('testing.show');
    Route::get('/create', [Order2Controller::class, 'create'])->name('testing.create');
    Route::post('/create', [Order2Controller::class, 'store'])->name('testing.store');
    Route::get('/edit/{order}', [Order2Controller::class, 'edit'])->name('testing.edit');
    Route::put('/update/{order}', [Order2Controller::class, 'update'])->name('testing.update');
    Route::delete('/delete/{order}', [Order2Controller::class, 'destroy'])->name('testing.destroy');
});



Route::redirect('/', 'shop')->name('home');

Route::get('shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('product/{product}', [ProductController::class, 'show'])->name('shop.product');
Route::get('product/{product}/{name}', [ProductController::class, 'show'])->name('shop.product');

Route::prefix('cart')
    ->group(function () {

    Route::get('/', [CartController::class, 'show'])->name('cart.show');
    Route::post('add/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::post('update/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('remove/{product}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('clear', [CartController::class, 'clear'])->name('cart.clear');
    Route::post('confirm', [CartController::class, 'confirm'])->name('cart.confirm');
    Route::post('ajax-update', [CartController::class, 'ajaxUpdate'])->name('cart.ajaxUpdate');
});

Route::middleware(['auth'])
    ->prefix("card")
    ->group(function () {

    Route::get('recharge', [CardController::class, 'form'])->name('card.recharge');
    Route::post('recharge', [CardController::class, 'recharge'])->name('card.recharge.submit');

    Route::get('/', [CardController::class, 'index'])->name('card.index');
    Route::get('export-csv', [App\Http\Controllers\CardController::class, 'exportCsv'])->name('card.export.csv')->middleware('auth');
    Route::get('membership', [CardController::class, 'membership'])->name('card.membership');
});

Route::middleware(['auth', Admin::class])
    ->prefix("metrics-history")
    ->group(function () {

    Route::get('transaction-records', [MetricsController::class, 'transactionRecords'])->name('metricsHistory.transactionRecords');
    Route::get('sales-performance', [MetricsController::class, 'salesPerformance'])->name('metricsHistory.salesPerformance');
    Route::get('membership-trends', [MetricsController::class, 'membershipTrends'])->name('metricsHistory.membershipTrends');

    Route::get('stock-history', [ProductController::class, 'stockHistory'])->name('metricsHistory.stockHistory');
});

Route::get('/my-orders', [OrderController::class, 'showUserOrders'])->name('orders.user');

Route::middleware(['auth', Management::class])
    ->prefix("management")
    ->group(function () {

    Route::resource('products', ProductController::class);
    Route::delete('products/{product}/photo', [ProductController::class, 'destroyPhoto'])->name('products.destroy.photo');

    Route::resource('categories', CategoryController::class);

    Route::get('stock', [ProductController::class, 'stock'])->name('products.stock');
    Route::put('stock', [ProductController::class, 'updateStock'])->name('products.stock.update');

    Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('order/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::put('order/{order}/confirm', [OrderController::class, 'confirm'])->name('orders.confirm');
    Route::put('order/{order}/cancel', [OrderController::class, 'cancel'])
        ->name('orders.cancel');
    Route::get('order/{order}/pdf', [OrderController::class, 'pdf'])->name('orders.pdf');
});

Route::middleware(['auth', Admin::class])
    ->prefix("business")
    ->group(function () {

    Volt::route('membership', 'business.membership')->name('business.membership');
    Volt::route('shipping', 'business.shipping')->name('business.shipping');
});

Route::middleware(['auth', Admin::class])
    ->prefix("users")
    ->group(function () {

    Route::get('members', [UserController::class, 'members'])->name('users.members');
    Route::get('employees', [UserController::class, 'employees'])->name('users.employees');
    Route::get('employees/register', [UserController::class, 'registerEmployee'])->name('users.employees.register');
    Route::post('employees', [UserController::class, 'saveEmployee'])->name('users.employees.save');
    Route::get('boards', [UserController::class, 'boards'])->name('users.boards');

    Route::get('{user}', [UserController::class, 'view'])->name('users.view');
    Route::get('{user}/edit', [UserController::class, 'edit'])->name('users.edit');

    Route::put('{user}/block', [UserController::class, 'block'])->name('users.block');
    Route::put('{user}/unblock', [UserController::class, 'unblock'])->name('users.unblock');

    Route::put('{user}/promote', [UserController::class, 'promote'])->name('users.promote');
    Route::put('{user}/demote', [UserController::class, 'demote'])->name('users.demote');

    Route::delete('{user}/delete', [UserController::class, 'delete'])->name('users.delete');

    Route::put('{user}', [UserController::class, 'update'])->name('users.update');
});

require __DIR__.'/auth.php';
