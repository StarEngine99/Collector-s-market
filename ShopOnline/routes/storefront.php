<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductImageController;
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
Route::get('/san-pham', [ProductSearchController::class, 'index'])->name('products.all');
Route::get('/phieu-giam-gia', [\App\Http\Controllers\CouponStorefrontController::class, 'index'])->name('coupons.index');
Route::post('/phieu-giam-gia/luu/{id}', [\App\Http\Controllers\CouponStorefrontController::class, 'claim'])
    ->whereNumber('id')
    ->name('coupons.claim');
Route::get('/danh-muc/{slug}', [CategoryController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('categories.show');
Route::get('/san-pham/{slug}', [ProductController::class, 'show'])
    ->where('slug', '[a-z0-9-]+')
    ->name('products.show');
Route::get('/product-images/{id}', [ProductImageController::class, 'show'])
    ->whereNumber('id')
    ->name('product-images.show');
Route::get('/tim-kiem', [ProductSearchController::class, 'index'])
    ->name('products.search');
Route::post('/tim-kiem-bang-anh', [\App\Http\Controllers\ImageSearchController::class, 'search'])
    ->name('products.image-search');
Route::post('/san-pham/{id}/yeu-thich', [\App\Http\Controllers\WishlistController::class, 'toggle'])
    ->whereNumber('id')
    ->name('products.favorite');
Route::post('/san-pham/{id}/danh-gia', [\App\Http\Controllers\ProductReviewController::class, 'store'])
    ->whereNumber('id')
    ->name('products.reviews.store');

Route::get('/gioi-thieu', [StaticPageController::class, 'about'])->name('pages.about');
Route::get('/chinh-sach-doi-tra', [StaticPageController::class, 'returnPolicy'])->name('pages.policy.return');
Route::get('/chinh-sach-van-chuyen', [StaticPageController::class, 'shippingPolicy'])->name('pages.policy.shipping');
Route::get('/chinh-sach-bao-mat', [StaticPageController::class, 'privacyPolicy'])->name('pages.policy.privacy');
Route::get('/lien-he', [StaticPageController::class, 'contact'])->name('pages.contact');
Route::post('/lien-he', [StaticPageController::class, 'submitContact'])->name('pages.contact.submit');
