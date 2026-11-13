<?php

use App\Http\Controllers\Admin\BannerController;
use App\Http\Controllers\Admin\PostController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Admin Content Routes (TV2 - B6)
|--------------------------------------------------------------------------
| Yêu cầu quyền content.manage từ hệ thống phân quyền của TV1.
| Nhúng file này vào routes/web.php bằng: require __DIR__.'/admin_content.php';
*/

Route::middleware(['auth', 'can:content.manage'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function (): void {
        Route::get('/bai-viet', [PostController::class, 'index'])->name('posts.index');
        Route::get('/bai-viet/tao', [PostController::class, 'create'])->name('posts.create');
        Route::post('/bai-viet', [PostController::class, 'store'])->name('posts.store');
        Route::get('/bai-viet/{id}/sua', [PostController::class, 'edit'])->whereNumber('id')->name('posts.edit');
        Route::put('/bai-viet/{id}', [PostController::class, 'update'])->whereNumber('id')->name('posts.update');
        Route::delete('/bai-viet/{id}', [PostController::class, 'destroy'])->whereNumber('id')->name('posts.destroy');

        Route::get('/banner', [BannerController::class, 'index'])->name('banners.index');
        Route::get('/banner/tao', [BannerController::class, 'create'])->name('banners.create');
        Route::post('/banner', [BannerController::class, 'store'])->name('banners.store');
        Route::get('/banner/{id}/sua', [BannerController::class, 'edit'])->whereNumber('id')->name('banners.edit');
        Route::put('/banner/{id}', [BannerController::class, 'update'])->whereNumber('id')->name('banners.update');
        Route::patch('/banner/{id}/thu-tu', [BannerController::class, 'updateOrder'])->whereNumber('id')->name('banners.order');
        Route::delete('/banner/{id}', [BannerController::class, 'destroy'])->whereNumber('id')->name('banners.destroy');
    });
