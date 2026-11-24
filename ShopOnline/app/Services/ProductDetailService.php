<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service xử lý trang chi tiết sản phẩm (A3): thông tin sản phẩm, bộ ảnh và sản phẩm liên quan.
 */
class ProductDetailService
{
    public function __construct(
        private readonly ProductPresentationService $productPresentation
    ) {
    }

    /**
     * Lấy dữ liệu chi tiết sản phẩm theo slug.
     *
     * @return array{
     *     product: object,
     *     images: Collection<int, object>,
     *     relatedProducts: Collection<int, object>
     * }|null
     */
    public function getProductDetail(string $slug, int $relatedLimit = 4): ?array
    {
        $product = DB::table('products')
            ->join('categories', 'categories.MaDanhMuc', '=', 'products.MaDanhMuc')
            ->select([
                'products.MaSanPham',
                'products.MaDanhMuc',
                'products.MaSKU',
                'products.TenSanPham',
                'products.DuongDan',
                'products.MoTa',
                'products.Gia',
                'products.SoLuongTon',
                'products.TrangThai',
                'products.NoiBat',
                'products.NgayDang',
                'categories.TenDanhMuc',
                'categories.DuongDan as DuongDanDanhMuc',
            ])
            ->where('products.DuongDan', $slug)
            ->whereIn('products.TrangThai', ['active', 'out_of_stock'])
            ->whereNull('products.NgayXoa')
            ->where('categories.TrangThai', 'active')
            ->whereNull('categories.NgayXoa')
            ->first();

        if ($product === null) {
            return null;
        }

        $enrichedProduct = $this->productPresentation
            ->enrichProducts(collect([$product]))
            ->first();

        $images = DB::table('product_images')
            ->select(['MaHinhAnh', 'DuongDanHinhAnh', 'MoTaHinhAnh', 'ThuTuSapXep'])
            ->where('MaSanPham', $product->MaSanPham)
            ->orderBy('ThuTuSapXep')
            ->orderBy('MaHinhAnh')
            ->get();

        $relatedRaw = $this->productPresentation->basePublicProductQuery()
            ->where('products.MaDanhMuc', $product->MaDanhMuc)
            ->where('products.MaSanPham', '!=', $product->MaSanPham)
            ->orderByDesc('products.NgayDang')
            ->orderByDesc('products.MaSanPham')
            ->limit($relatedLimit)
            ->get();

        $reviews = DB::table('product_reviews')
            ->leftJoin('users', 'users.MaNguoiDung', '=', 'product_reviews.MaNguoiDung')
            ->select([
                'product_reviews.MaDanhGia',
                'product_reviews.SoSao',
                'product_reviews.NoiDung',
                'product_reviews.NgayTao',
                'users.HoTen',
                'users.Avatar',
            ])
            ->where('product_reviews.MaSanPham', $product->MaSanPham)
            ->whereIn('product_reviews.TrangThai', ['published', 'approved'])
            ->orderByDesc('product_reviews.NgayTao')
            ->get();

        $avgRating = $enrichedProduct->DiemDanhGia;
        $reviewsCount = $enrichedProduct->SoDanhGia;

        $userId = auth()->id();
        $isFavorited = false;
        if ($userId) {
            $isFavorited = DB::table('wishlists')
                ->where('MaNguoiDung', $userId)
                ->where('MaSanPham', $product->MaSanPham)
                ->exists();
        }

        $soldCount = $enrichedProduct->DaBan;

        return [
            'product' => $enrichedProduct,
            'images' => $images,
            'relatedProducts' => $this->productPresentation->enrichProducts($relatedRaw),
            'reviews' => $reviews,
            'avgRating' => $avgRating,
            'reviewsCount' => $reviewsCount,
            'isFavorited' => $isFavorited,
            'soldCount' => $soldCount,
        ];
    }
}
