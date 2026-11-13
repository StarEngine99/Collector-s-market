<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Cart & Checkout Routes (TV3 - A5)
|--------------------------------------------------------------------------
| Nhúng file này vào routes/web.php bằng: require __DIR__.'/cart_checkout.php';
*/

// Giỏ hàng: cho phép cả khách chưa đăng nhập (lưu theo phiên) và người dùng đã đăng nhập
Route::get('/gio-hang', [CartController::class, 'index'])->name('cart.index');
Route::post('/gio-hang/them', [CartController::class, 'add'])->name('cart.add');
Route::patch('/gio-hang/cap-nhat/{itemId}', [CartController::class, 'update'])
    ->whereNumber('itemId')
    ->name('cart.update');
Route::delete('/gio-hang/xoa/{itemId}', [CartController::class, 'remove'])
    ->whereNumber('itemId')
    ->name('cart.remove');
Route::delete('/gio-hang/xoa-tat-ca', [CartController::class, 'clear'])->name('cart.clear');

Route::post('/gio-hang/ma-giam-gia', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::delete('/gio-hang/ma-giam-gia', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');

// Thanh toán & Tạo đơn: bắt buộc phải đăng nhập
Route::middleware('auth')->group(function (): void {
    Route::get('/thanh-toan', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/thanh-toan', [CheckoutController::class, 'store'])->name('checkout.store');
    Route::get('/thanh-toan/thanh-cong/{orderNumber}', [CheckoutController::class, 'success'])
        ->where('orderNumber', '[A-Za-z0-9-]+')
        ->name('checkout.success');
});
