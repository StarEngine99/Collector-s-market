<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\AccountAddressController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Account & Order History Routes (TV3 - A7)
|--------------------------------------------------------------------------
| Nhúng file này vào routes/web.php bằng: require __DIR__.'/account.php';
*/

Route::middleware('auth')
    ->prefix('tai-khoan')
    ->name('account.')
    ->group(function (): void {
        Route::get('/', [AccountController::class, 'index'])->name('index');
        Route::put('/thong-tin', [AccountController::class, 'updateProfile'])->name('profile.update');
        Route::put('/doi-mat-khau', [AccountController::class, 'changePassword'])->name('password.update');
        Route::post('/dia-chi', [AccountAddressController::class, 'store'])->name('addresses.store');
        Route::put('/dia-chi/{id}', [AccountAddressController::class, 'update'])
            ->whereNumber('id')
            ->name('addresses.update');
        Route::patch('/dia-chi/{id}/mac-dinh', [AccountAddressController::class, 'makeDefault'])
            ->whereNumber('id')
            ->name('addresses.default');
        Route::delete('/dia-chi/{id}', [AccountAddressController::class, 'destroy'])
            ->whereNumber('id')
            ->name('addresses.destroy');

        Route::get('/don-hang', [AccountController::class, 'orders'])->name('orders.index');
        Route::get('/don-hang/{orderNumber}', [AccountController::class, 'showOrder'])
            ->where('orderNumber', '[A-Za-z0-9-]+')
            ->name('orders.show');
        Route::post('/don-hang/{orderNumber}/huy', [AccountController::class, 'cancelOrder'])
            ->where('orderNumber', '[A-Za-z0-9-]+')
            ->name('orders.cancel');
    });

