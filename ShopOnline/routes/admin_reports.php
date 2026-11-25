<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\ReviewController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Reports Routes (TV2 - B7)
|--------------------------------------------------------------------------
| Yêu cầu quyền reports.view từ hệ thống phân quyền của TV1.
| Nhúng file này vào routes/web.php bằng: require __DIR__.'/admin_reports.php';
*/

Route::middleware(['auth', 'can:users.manage'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/danh-gia', [ReviewController::class, 'index'])->name('reviews.index');
    });

Route::middleware(['auth', 'can:reports.view'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/thong-ke', [ReportController::class, 'index'])->name('reports.index');
    });
