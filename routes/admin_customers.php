<?php

use App\Http\Controllers\Admin\CustomerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Customer Management Routes (B4 - Quản lý khách hàng)
|--------------------------------------------------------------------------
| Sử dụng layout quản trị chung `layouts.admin` của nhóm.
*/

Route::prefix('admin')->name('admin.')->middleware(['auth', 'can:customers.view'])->group(function () {
    Route::get('/khach-hang', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/khach-hang/{id}', [CustomerController::class, 'show'])->whereNumber('id')->name('customers.show');
    Route::patch('/khach-hang/{id}/trang-thai', [CustomerController::class, 'updateStatus'])
        ->whereNumber('id')
        ->middleware('can:customers.lock')
        ->name('customers.status');
});

