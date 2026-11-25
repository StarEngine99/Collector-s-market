<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReviewController extends Controller
{
    public function index(): View
    {
        $reviews = DB::table('product_reviews')
            ->join('products', 'products.MaSanPham', '=', 'product_reviews.MaSanPham')
            ->leftJoin('users', 'users.MaNguoiDung', '=', 'product_reviews.MaNguoiDung')
            ->select([
                'product_reviews.MaDanhGia',
                'product_reviews.SoSao',
                'product_reviews.NoiDung',
                'product_reviews.TrangThai',
                'product_reviews.NgayTao',
                'products.TenSanPham',
                'products.MaSKU',
                'users.HoTen',
                'users.Email',
            ])
            ->orderByDesc('product_reviews.NgayTao')
            ->paginate(20);

        return view('admin.reviews.index', compact('reviews'));
    }
}
