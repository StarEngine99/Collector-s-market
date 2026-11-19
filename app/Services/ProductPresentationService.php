<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service dùng chung cho truy vấn sản phẩm công khai, ảnh đại diện và giá khuyến mãi.
 */
class ProductPresentationService
{
    /**
     * Query cơ sở cho sản phẩm công khai (không ẩn, chưa xóa mềm, danh mục đang hoạt động).
     */
    public function basePublicProductQuery(): Builder
    {
        return DB::table('products')
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
            ->where('products.TrangThai', 'active')
            ->whereNull('products.NgayXoa')
            ->where('categories.TrangThai', 'active')
            ->whereNull('categories.NgayXoa');
    }

    /**
     * Gắn ảnh đại diện đầu tiên và giá khuyến mãi đang hiệu lực cho danh sách sản phẩm.
     *
     * @param Collection<int, object> $products
     * @return Collection<int, object>
     */
    public function enrichProducts(Collection $products): Collection
    {
        if ($products->isEmpty()) {
            return $products;
        }

        $productIds = $products->pluck('MaSanPham')->all();
        $imagesByProduct = $this->getPrimaryImagesMap($productIds);
        $promotionsByProduct = $this->getActivePromotionsMap($productIds);

        return $products->map(function (object $product) use ($imagesByProduct, $promotionsByProduct): object {
            $image = $imagesByProduct->get($product->MaSanPham);
            $product->HinhAnhDaiDien = $image->DuongDanHinhAnh ?? null;
            $product->MoTaHinhAnh = $image->MoTaHinhAnh ?? $product->TenSanPham;

            $pricing = $this->calculateDiscountedPrice(
                (float) $product->Gia,
                $promotionsByProduct->get($product->MaSanPham, collect())
            );

            $product->GiaGoc = $pricing['gia_goc'];
            $product->GiaSauGiam = $pricing['gia_sau_giam'];
            $product->CoGiamGia = $pricing['co_giam_gia'];
            $product->PhanTramGiam = $pricing['phan_tram_giam'];

            return $product;
        });
    }

    /**
     * Lấy ảnh có ThuTuSapXep nhỏ nhất cho mỗi sản phẩm.
     *
     * @param array<int, int|string> $productIds
     * @return Collection<int|string, object>
     */
    private function getPrimaryImagesMap(array $productIds): Collection
    {
        return DB::table('product_images')
            ->select(['MaSanPham', 'DuongDanHinhAnh', 'MoTaHinhAnh'])
            ->whereIn('MaSanPham', $productIds)
            ->orderBy('ThuTuSapXep')
            ->orderBy('MaHinhAnh')
            ->get()
            ->unique('MaSanPham')
            ->keyBy('MaSanPham');
    }

    /**
     * Lấy các chương trình khuyến mãi đang active và trong thời gian hiệu lực theo sản phẩm.
     *
     * @param array<int, int|string> $productIds
     * @return Collection<int|string, Collection<int, object>>
     */
    private function getActivePromotionsMap(array $productIds): Collection
    {
        $now = Carbon::now();

        return DB::table('promotion_products')
            ->join('promotions', 'promotions.MaKhuyenMai', '=', 'promotion_products.MaKhuyenMai')
            ->select([
                'promotion_products.MaSanPham',
                'promotions.LoaiGiamGia',
                'promotions.GiaTriGiam',
            ])
            ->whereIn('promotion_products.MaSanPham', $productIds)
            ->where('promotions.TrangThai', 'active')
            ->where('promotions.BatDauLuc', '<=', $now)
            ->where('promotions.KetThucLuc', '>=', $now)
            ->get()
            ->groupBy('MaSanPham');
    }

    /**
     * Tính giá tốt nhất cho khách hàng nếu sản phẩm thuộc nhiều khuyến mãi.
     *
     * @param Collection<int, object> $promotions
     * @return array{gia_goc: float, gia_sau_giam: float, co_giam_gia: bool, phan_tram_giam: int}
     */
    private function calculateDiscountedPrice(float $originalPrice, Collection $promotions): array
    {
        $bestPrice = $originalPrice;

        foreach ($promotions as $promotion) {
            $value = (float) $promotion->GiaTriGiam;
            $candidate = match ($promotion->LoaiGiamGia) {
                'percent' => max(0.0, $originalPrice * (1 - ($value / 100))),
                'fixed' => max(0.0, $originalPrice - $value),
                default => $originalPrice,
            };

            if ($candidate < $bestPrice) {
                $bestPrice = $candidate;
            }
        }

        $hasDiscount = $originalPrice > 0 && $bestPrice < $originalPrice;
        $discountPercent = $hasDiscount
            ? (int) round((($originalPrice - $bestPrice) / $originalPrice) * 100)
            : 0;

        return [
            'gia_goc' => $originalPrice,
            'gia_sau_giam' => $bestPrice,
            'co_giam_gia' => $hasDiscount,
            'phan_tram_giam' => $discountPercent,
        ];
    }
}
