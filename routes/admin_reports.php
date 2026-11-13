<?php

use App\Http\Controllers\Admin\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Reports Routes (TV2 - B7)
|--------------------------------------------------------------------------
| Yêu cầu quyền reports.view từ hệ thống phân quyền của TV1.
| Nhúng file này vào routes/web.php bằng: require __DIR__.'/admin_reports.php';
*/

Route::middleware(['auth', 'can:reports.view'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/thong-ke', [ReportController::class, 'index'])->name('reports.index');
    });
