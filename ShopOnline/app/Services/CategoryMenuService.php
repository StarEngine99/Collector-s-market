<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service cung cấp dữ liệu menu danh mục công khai cho header.
 */
class CategoryMenuService
{
    /**
     * Lấy cây danh mục đang hoạt động (chưa xóa mềm), sắp xếp theo thứ tự hiển thị.
     *
     * @return Collection<int, object>
     */
    public function getActiveMenuTree(): Collection
    {
        $categories = DB::table('categories')
            ->select([
                'MaDanhMuc',
                'MaDanhMucCha',
                'TenDanhMuc',
                'DuongDan',
                'ThuTuSapXep',
            ])
            ->where('TrangThai', 'active')
            ->whereNull('NgayXoa')
            ->orderBy('ThuTuSapXep')
            ->orderBy('MaDanhMuc')
            ->get();

        $groupedByParent = $categories->groupBy(function (object $category): int {
            return (int) ($category->MaDanhMucCha ?? 0);
        });

        return $groupedByParent->get(0, collect())->map(function (object $parent) use ($groupedByParent): object {
            $parent->children = $groupedByParent->get((int) $parent->MaDanhMuc, collect());

            return $parent;
        });
    }
}
