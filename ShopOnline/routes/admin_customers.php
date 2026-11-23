<?php

use App\Http\Controllers\Admin\CustomerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Customer Management Routes (B4 - Quản lý khách hàng)
|--------------------------------------------------------------------------
| Sử dụng layout quản trị chung `layouts.admin` của nhóm.
*/

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function () {
    Route::middleware('can:customers.view')->group(function (): void {
        Route::get('/khach-hang', [CustomerController::class, 'index'])->name('customers.index');
        Route::get('/khach-hang/{id}', [CustomerController::class, 'show'])
            ->whereNumber('id')
            ->name('customers.show');
        Route::patch('/khach-hang/{id}/trang-thai', [CustomerController::class, 'updateStatus'])
            ->whereNumber('id')
            ->middleware('can:customers.lock')
            ->name('customers.status');
    });

    Route::middleware('can:users.manage')->group(function (): void {
        Route::get('/khach-hang/tao', [CustomerController::class, 'create'])->name('customers.create');
        Route::post('/khach-hang', [CustomerController::class, 'store'])->name('customers.store');
        Route::get('/khach-hang/{id}/sua', [CustomerController::class, 'edit'])
            ->whereNumber('id')
            ->name('customers.edit');
        Route::put('/khach-hang/{id}', [CustomerController::class, 'update'])
            ->whereNumber('id')
            ->name('customers.update');
        Route::delete('/khach-hang/{id}', [CustomerController::class, 'destroy'])
            ->whereNumber('id')
            ->name('customers.destroy');
    });
});

