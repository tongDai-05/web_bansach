<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\OrderController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('books.index');
});

Auth::routes();

Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
Route::resource('books', BookController::class);
// Các route cho Giỏ hàng
Route::controller(CartController::class)->group(function () {
    Route::get('/cart', 'index')->name('cart.index');
    Route::post('/cart/add', 'store')->name('cart.store');
    Route::post('/cart/update/{item}', 'update')->name('cart.update'); // Để cập nhật số lượng
    Route::delete('/cart/remove/{item}', 'destroy')->name('cart.destroy'); // Để xóa item
});
// Các route cho Đơn hàng (checkout)
Route::get('/checkout', [OrderController::class, 'checkout'])->name('checkout');
Route::post('/order', [OrderController::class, 'processOrder'])->name('order.process');
Route::get('/order/success/{order}', [OrderController::class, 'orderSuccess'])->name('order.success');
