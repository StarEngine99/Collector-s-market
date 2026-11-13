<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service cung cấp dữ liệu cho trang chủ (A1): banner trượt, sản phẩm nổi bật, sản phẩm mới.
 */
class HomeService
{
    public function __construct(
        private readonly ProductPresentationService $productPresentation
    ) {
    }

    /**
     * Lấy danh sách banner đang bật và trong thời gian hiển thị.
     *
     * @return Collection<int, object>
     */
    public function getActiveBanners(): Collection
    {
        $now = Carbon::now();

        return DB::table('banners')
            ->select([
                'MaBanner',
                'TieuDe',
                'DuongDanHinhAnh',
                'DuongDanDich',
                'ThuTuSapXep',
            ])
            ->where('TrangThai', 'active')
            ->where(function ($query) use ($now): void {
                $query->whereNull('BatDauLuc')->orWhere('BatDauLuc', '<=', $now);
            })
            ->where(function ($query) use ($now): void {
                $query->whereNull('KetThucLuc')->orWhere('KetThucLuc', '>=', $now);
            })
            ->orderBy('ThuTuSapXep')
            ->orderBy('MaBanner')
            ->get();
    }

    /**
     * Lấy sản phẩm nổi bật đang bán.
     *
     * @return Collection<int, object>
     */
    public function getFeaturedProducts(int $limit = 8): Collection
    {
        $products = $this->productPresentation->basePublicProductQuery()
            ->where('products.NoiBat', 1)
            ->orderByDesc('products.NgayDang')
            ->orderByDesc('products.MaSanPham')
            ->limit($limit)
            ->get();

        return $this->productPresentation->enrichProducts($products);
    }

    /**
     * Lấy sản phẩm mới đăng gần nhất.
     *
     * @return Collection<int, object>
     */
    public function getNewProducts(int $limit = 8): Collection
    {
        $products = $this->productPresentation->basePublicProductQuery()
            ->orderByDesc('products.NgayDang')
            ->orderByDesc('products.MaSanPham')
            ->limit($limit)
            ->get();

        return $this->productPresentation->enrichProducts($products);
    }
}
