<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service CRUD bài viết cho quản trị viên (B6).
 */
class PostManagementService
{
    /**
     * Lấy danh sách bài viết có lọc và phân trang.
     */
    public function paginatePosts(?string $keyword = null, ?string $status = null, int $perPage = 15): LengthAwarePaginator
    {
        $query = DB::table('posts')
            ->leftJoin('users', 'users.MaNguoiDung', '=', 'posts.MaTacGia')
            ->select([
                'posts.MaBaiViet',
                'posts.TieuDe',
                'posts.DuongDan',
                'posts.TrangThai',
                'posts.NgayDang',
                'posts.NgayCapNhat',
                'users.HoTen as TenTacGia',
            ]);

        $trimmed = trim((string) $keyword);
        if ($trimmed !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $trimmed);
            $query->where('posts.TieuDe', 'like', '%' . $escaped . '%');
        }

        if ($status !== null && in_array($status, ['draft', 'published', 'hidden'], true)) {
            $query->where('posts.TrangThai', $status);
        }

        return $query
            ->orderByDesc('posts.NgayCapNhat')
            ->orderByDesc('posts.MaBaiViet')
            ->paginate($perPage)
            ->withQueryString();
    }

    public function findById(int $id): ?object
    {
        return DB::table('posts')->where('MaBaiViet', $id)->first();
    }

    /**
     * Tạo bài viết mới.
     *
     * @param array<string, mixed> $data
     */
    public function create(array $data, ?int $authorId): int
    {
        $now = Carbon::now();
        $publishedAt = $this->resolvePublishedAt($data['TrangThai'], $data['NgayDang'] ?? null, $now);

        return (int) DB::table('posts')->insertGetId([
            'MaTacGia' => $authorId,
            'TieuDe' => $data['TieuDe'],
            'DuongDan' => $data['DuongDan'],
            'NoiDung' => $data['NoiDung'],
            'TrangThai' => $data['TrangThai'],
            'NgayDang' => $publishedAt,
            'NgayTao' => $now,
            'NgayCapNhat' => $now,
        ]);
    }

    /**
     * Cập nhật bài viết.
     *
     * @param array<string, mixed> $data
     */
    public function update(int $id, array $data): bool
    {
        $now = Carbon::now();
        $publishedAt = $this->resolvePublishedAt($data['TrangThai'], $data['NgayDang'] ?? null, $now);

        $affected = DB::table('posts')
            ->where('MaBaiViet', $id)
            ->update([
                'TieuDe' => $data['TieuDe'],
                'DuongDan' => $data['DuongDan'],
                'NoiDung' => $data['NoiDung'],
                'TrangThai' => $data['TrangThai'],
                'NgayDang' => $publishedAt,
                'NgayCapNhat' => $now,
            ]);

        return $affected >= 0;
    }

    public function delete(int $id): bool
    {
        return DB::table('posts')->where('MaBaiViet', $id)->delete() > 0;
    }

    private function resolvePublishedAt(string $status, mixed $inputDate, Carbon $now): ?string
    {
        if (!empty($inputDate)) {
            return Carbon::parse((string) $inputDate)->format('Y-m-d H:i:s');
        }

        return $status === 'published' ? $now->format('Y-m-d H:i:s') : null;
    }
}
