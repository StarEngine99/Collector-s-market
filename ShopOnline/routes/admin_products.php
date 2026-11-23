<?php

use App\Http\Controllers\Admin\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('admin')->name('admin.')->middleware('auth')->group(function (): void {
    Route::get('/san-pham', [ProductController::class, 'index'])
        ->middleware('can:products.view')
        ->name('products.index');
    Route::get('/san-pham/tao', [ProductController::class, 'create'])
        ->middleware('can:products.create')
        ->name('products.create');
    Route::post('/san-pham', [ProductController::class, 'store'])
        ->middleware('can:products.create')
        ->name('products.store');
    Route::get('/san-pham/{id}/sua', [ProductController::class, 'edit'])
        ->whereNumber('id')
        ->middleware('can:products.update')
        ->name('products.edit');
    Route::put('/san-pham/{id}', [ProductController::class, 'update'])
        ->whereNumber('id')
        ->middleware('can:products.update')
        ->name('products.update');
    Route::delete('/san-pham/{id}', [ProductController::class, 'destroy'])
        ->whereNumber('id')
        ->middleware('can:products.delete')
        ->name('products.destroy');
});
