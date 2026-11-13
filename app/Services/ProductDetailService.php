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

        return [
            'product' => $enrichedProduct,
            'images' => $images,
            'relatedProducts' => $this->productPresentation->enrichProducts($relatedRaw),
        ];
    }
}
