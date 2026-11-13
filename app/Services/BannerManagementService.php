<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Service CRUD banner trang chủ và quản lý thứ tự hiển thị (B6).
 */
class BannerManagementService
{
    public function paginateBanners(int $perPage = 15): LengthAwarePaginator
    {
        return DB::table('banners')
            ->select([
                'MaBanner',
                'TieuDe',
                'DuongDanHinhAnh',
                'DuongDanDich',
                'ThuTuSapXep',
                'BatDauLuc',
                'KetThucLuc',
                'TrangThai',
                'NgayCapNhat',
            ])
            ->orderBy('ThuTuSapXep')
            ->orderByDesc('MaBanner')
            ->paginate($perPage);
    }

    public function findById(int $id): ?object
    {
        return DB::table('banners')->where('MaBanner', $id)->first();
    }

    /**
     * Tạo banner mới và lưu ảnh vào disk public.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data, UploadedFile $image, ?int $managerId): int
    {
        $now = Carbon::now();
        $storedPath = $this->storeBannerImage($image);

        return (int) DB::table('banners')->insertGetId([
            'MaNguoiQuanLy' => $managerId,
            'TieuDe' => $data['TieuDe'],
            'DuongDanHinhAnh' => $storedPath,
            'DuongDanDich' => $data['DuongDanDich'] ?? null,
            'ThuTuSapXep' => (int) $data['ThuTuSapXep'],
            'BatDauLuc' => !empty($data['BatDauLuc']) ? Carbon::parse((string) $data['BatDauLuc'])->format('Y-m-d H:i:s') : null,
            'KetThucLuc' => !empty($data['KetThucLuc']) ? Carbon::parse((string) $data['KetThucLuc'])->format('Y-m-d H:i:s') : null,
            'TrangThai' => $data['TrangThai'],
            'NgayTao' => $now,
            'NgayCapNhat' => $now,
        ]);
    }

    /**
     * Cập nhật banner; nếu có ảnh mới sẽ xóa ảnh cũ trên disk.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data, ?UploadedFile $image = null): bool
    {
        $existing = $this->findById($id);
        if ($existing === null) {
            return false;
        }

        $imagePath = $existing->DuongDanHinhAnh;
        if ($image !== null) {
            $newPath = $this->storeBannerImage($image);
            $this->deleteLocalImage($existing->DuongDanHinhAnh);
            $imagePath = $newPath;
        }

        DB::table('banners')
            ->where('MaBanner', $id)
            ->update([
                'TieuDe' => $data['TieuDe'],
                'DuongDanHinhAnh' => $imagePath,
                'DuongDanDich' => $data['DuongDanDich'] ?? null,
                'ThuTuSapXep' => (int) $data['ThuTuSapXep'],
                'BatDauLuc' => !empty($data['BatDauLuc']) ? Carbon::parse((string) $data['BatDauLuc'])->format('Y-m-d H:i:s') : null,
                'KetThucLuc' => !empty($data['KetThucLuc']) ? Carbon::parse((string) $data['KetThucLuc'])->format('Y-m-d H:i:s') : null,
                'TrangThai' => $data['TrangThai'],
                'NgayCapNhat' => Carbon::now(),
            ]);

        return true;
    }

    public function updateOrder(int $id, int $order): bool
    {
        return DB::table('banners')
            ->where('MaBanner', $id)
            ->update([
                'ThuTuSapXep' => max(0, $order),
                'NgayCapNhat' => Carbon::now(),
            ]) > 0;
    }

    public function delete(int $id): bool
    {
        $existing = $this->findById($id);
        if ($existing === null) {
            return false;
        }

        $deleted = DB::table('banners')->where('MaBanner', $id)->delete() > 0;
        if ($deleted) {
            $this->deleteLocalImage($existing->DuongDanHinhAnh);
        }

        return $deleted;
    }

    private function storeBannerImage(UploadedFile $image): string
    {
        $extension = strtolower($image->extension() ?: 'jpg');
        $filename = Str::uuid()->toString() . '.' . $extension;
        $relativePath = Storage::disk('public')->putFileAs('banners', $image, $filename);

        return 'storage/' . ltrim((string) $relativePath, '/');
    }

    private function deleteLocalImage(?string $publicPath): void
    {
        if (empty($publicPath) || !str_starts_with($publicPath, 'storage/')) {
            return;
        }

        $diskPath = substr($publicPath, strlen('storage/'));
        if ($diskPath !== '' && Storage::disk('public')->exists($diskPath)) {
            Storage::disk('public')->delete($diskPath);
        }
    }
}
