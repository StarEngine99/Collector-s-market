<?php

use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\PromotionController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Promotions & Coupons Routes (TV3 - B5)
|--------------------------------------------------------------------------
| Yêu cầu quyền coupons.manage từ hệ thống phân quyền của TV1.
| Nhúng file này vào routes/web.php bằng: require __DIR__.'/admin_promotions.php';
*/

Route::middleware(['auth', 'can:coupons.manage'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        // Quản lý Mã giảm giá (coupons)
        Route::get('/ma-giam-gia', [CouponController::class, 'index'])->name('coupons.index');
        Route::get('/ma-giam-gia/tao', [CouponController::class, 'create'])->name('coupons.create');
        Route::post('/ma-giam-gia', [CouponController::class, 'store'])->name('coupons.store');
        Route::get('/ma-giam-gia/{id}/sua', [CouponController::class, 'edit'])->whereNumber('id')->name('coupons.edit');
        Route::put('/ma-giam-gia/{id}', [CouponController::class, 'update'])->whereNumber('id')->name('coupons.update');
        Route::delete('/ma-giam-gia/{id}', [CouponController::class, 'destroy'])->whereNumber('id')->name('coupons.destroy');

        // Quản lý Khuyến mãi theo sản phẩm (promotions)
        Route::get('/khuyen-mai', [PromotionController::class, 'index'])->name('promotions.index');
        Route::get('/khuyen-mai/tao', [PromotionController::class, 'create'])->name('promotions.create');
        Route::post('/khuyen-mai', [PromotionController::class, 'store'])->name('promotions.store');
        Route::get('/khuyen-mai/{id}/sua', [PromotionController::class, 'edit'])->whereNumber('id')->name('promotions.edit');
        Route::put('/khuyen-mai/{id}', [PromotionController::class, 'update'])->whereNumber('id')->name('promotions.update');
        Route::delete('/khuyen-mai/{id}', [PromotionController::class, 'destroy'])->whereNumber('id')->name('promotions.destroy');
    });

