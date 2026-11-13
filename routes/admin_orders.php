<?php

use App\Http\Controllers\Admin\OrderController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Orders Routes (TV3 - B3)
|--------------------------------------------------------------------------
| Yêu cầu quyền orders.view và orders.update-status từ hệ thống phân quyền của TV1.
| Nhúng file này vào routes/web.php bằng: require __DIR__.'/admin_orders.php';
*/

Route::middleware(['auth', 'can:orders.view'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/don-hang', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/don-hang/{id}', [OrderController::class, 'show'])
            ->whereNumber('id')
            ->name('orders.show');
        Route::get('/don-hang/{id}/in', [OrderController::class, 'print'])
            ->whereNumber('id')
            ->name('orders.print');

        Route::patch('/don-hang/{id}/trang-thai', [OrderController::class, 'updateStatus'])
            ->whereNumber('id')
            ->middleware('can:orders.update-status')
            ->name('orders.status');

        Route::patch('/don-hang/{id}/thanh-toan', [OrderController::class, 'updatePaymentStatus'])
            ->whereNumber('id')
            ->middleware('can:orders.update-status')
            ->name('orders.payment');
    });
