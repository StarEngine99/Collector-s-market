<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service quản lý Chương trình khuyến mãi theo sản phẩm phía quản trị (B5 - bảng `promotions` & `promotion_products`).
 */
class PromotionManagementService
{
    /**
     * Lấy danh sách chương trình khuyến mãi có lọc, phân trang và đếm số sản phẩm áp dụng.
     */
    public function paginatePromotions(
        ?string $keyword = null,
        ?string $status = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $productCountSub = DB::table('promotion_products')
            ->selectRaw('MaKhuyenMai, COUNT(*) as so_san_pham')
            ->groupBy('MaKhuyenMai');

        $query = DB::table('promotions')
            ->leftJoinSub($productCountSub, 'pp_counts', function ($join): void {
                $join->on('pp_counts.MaKhuyenMai', '=', 'promotions.MaKhuyenMai');
            })
            ->select([
                'promotions.MaKhuyenMai',
                'promotions.TenKhuyenMai',
                'promotions.MoTa',
                'promotions.LoaiGiamGia',
                'promotions.GiaTriGiam',
                'promotions.BatDauLuc',
                'promotions.KetThucLuc',
                'promotions.TrangThai',
                'promotions.NgayTao',
                'promotions.NgayCapNhat',
                DB::raw('COALESCE(pp_counts.so_san_pham, 0) as so_san_pham'),
            ]);

        $trimmed = trim((string) $keyword);
        if ($trimmed !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $trimmed);
            $query->where('promotions.TenKhuyenMai', 'like', '%' . $escaped . '%');
        }

        if ($status !== null && in_array($status, ['scheduled', 'active', 'inactive'], true)) {
            $query->where('promotions.TrangThai', $status);
        }

        return $query
            ->orderByDesc('promotions.NgayCapNhat')
            ->orderByDesc('promotions.MaKhuyenMai')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id): ?object
    {
        return DB::table('promotions')->where('MaKhuyenMai', $id)->first();
    }

    /**
     * Lấy danh sách ID sản phẩm đang thuộc chương trình khuyến mãi.
     *
     * @return list<int>
     */
    public function getAssignedProductIds(int $promotionId): array
    {
        return DB::table('promotion_products')
            ->where('MaKhuyenMai', $promotionId)
            ->pluck('MaSanPham')
            ->map(static fn ($id): int => (int) $id)
            ->all();
    }

    /**
     * Lấy danh sách sản phẩm chưa xóa mềm để quản trị viên chọn áp dụng khuyến mãi.
     *
     * @return Collection<int, object>
     */
    public function getSelectableProducts(): Collection
    {
        return DB::table('products')
            ->join('categories', 'categories.MaDanhMuc', '=', 'products.MaDanhMuc')
            ->select([
                'products.MaSanPham',
                'products.MaSKU',
                'products.TenSanPham',
                'products.Gia',
                'products.SoLuongTon',
                'products.TrangThai',
                'categories.TenDanhMuc',
            ])
            ->whereNull('products.NgayXoa')
            ->orderBy('categories.ThuTuSapXep')
            ->orderBy('products.TenSanPham')
            ->get();
    }

    /**
     * Tạo chương trình khuyến mãi mới và gắn danh sách sản phẩm trong một giao dịch.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data): int
    {
        return DB::transaction(function () use ($data): int {
            $now = Carbon::now();

            $promotionId = (int) DB::table('promotions')->insertGetId([
                'TenKhuyenMai' => trim((string) $data['TenKhuyenMai']),
                'MoTa' => !empty($data['MoTa']) ? trim((string) $data['MoTa']) : null,
                'LoaiGiamGia' => $data['LoaiGiamGia'],
                'GiaTriGiam' => (float) $data['GiaTriGiam'],
                'BatDauLuc' => Carbon::parse((string) $data['BatDauLuc'])->format('Y-m-d H:i:s'),
                'KetThucLuc' => Carbon::parse((string) $data['KetThucLuc'])->format('Y-m-d H:i:s'),
                'TrangThai' => $data['TrangThai'],
                'NgayTao' => $now,
                'NgayCapNhat' => $now,
            ]);

            $this->syncPromotionProducts($promotionId, $data['san_pham_ids'] ?? []);

            return $promotionId;
        });
    }

    /**
     * Cập nhật chương trình khuyến mãi và đồng bộ lại sản phẩm được áp dụng.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        return DB::transaction(function () use ($id, $data): bool {
            $now = Carbon::now();

            $affected = DB::table('promotions')
                ->where('MaKhuyenMai', $id)
                ->update([
                    'TenKhuyenMai' => trim((string) $data['TenKhuyenMai']),
                    'MoTa' => !empty($data['MoTa']) ? trim((string) $data['MoTa']) : null,
                    'LoaiGiamGia' => $data['LoaiGiamGia'],
                    'GiaTriGiam' => (float) $data['GiaTriGiam'],
                    'BatDauLuc' => Carbon::parse((string) $data['BatDauLuc'])->format('Y-m-d H:i:s'),
                    'KetThucLuc' => Carbon::parse((string) $data['KetThucLuc'])->format('Y-m-d H:i:s'),
                    'TrangThai' => $data['TrangThai'],
                    'NgayCapNhat' => $now,
                ]);

            $this->syncPromotionProducts($id, $data['san_pham_ids'] ?? []);

            return $affected >= 0;
        });
    }

    /**
     * Xóa chương trình khuyến mãi (bảng nối `promotion_products` tự động xóa theo ON DELETE CASCADE).
     */
    public function delete(int $id): bool
    {
        return DB::table('promotions')->where('MaKhuyenMai', $id)->delete() > 0;
    }

    /**
     * Đồng bộ bảng nối `promotion_products`.
     *
     * @param array<int, int|string> $productIds
     */
    private function syncPromotionProducts(int $promotionId, array $productIds): void
    {
        DB::table('promotion_products')
            ->where('MaKhuyenMai', $promotionId)
            ->delete();

        $uniqueIds = array_values(array_unique(array_map('intval', $productIds)));
        if ($uniqueIds === []) {
            return;
        }

        $rows = [];
        foreach ($uniqueIds as $productId) {
            if ($productId > 0) {
                $rows[] = [
                    'MaKhuyenMai' => $promotionId,
                    'MaSanPham' => $productId,
                ];
            }
        }

        if ($rows !== []) {
            DB::table('promotion_products')->insert($rows);
        }
    }
}

