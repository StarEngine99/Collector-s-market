<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductSearchController;
use App\Http\Controllers\StaticPageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront Routes (TV2)
|--------------------------------------------------------------------------
| Nhúng file này vào routes/web.php bằng: require __DIR__.'/storefront.php';
*/

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/danh-muc/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('categories.show');
Route::get('/san-pham/{slug}', [ProductController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('products.show');
Route::get('/tim-kiem', [ProductSearchController::class, 'index'])
    ->name('products.search');

Route::get('/gioi-thieu', [StaticPageController::class, 'about'])->name('pages.about');
Route::get('/chinh-sach-doi-tra', [StaticPageController::class, 'returnPolicy'])->name('pages.policy.return');
Route::get('/chinh-sach-van-chuyen', [StaticPageController::class, 'shippingPolicy'])->name('pages.policy.shipping');
Route::get('/chinh-sach-bao-mat', [StaticPageController::class, 'privacyPolicy'])->name('pages.policy.privacy');
Route::get('/lien-he', [StaticPageController::class, 'contact'])->name('pages.contact');
Route::post('/lien-he', [StaticPageController::class, 'submitContact'])->name('pages.contact.submit');
