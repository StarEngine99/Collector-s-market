<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Service xử lý trang danh mục sản phẩm (A2): thông tin danh mục, danh mục con và phân trang sản phẩm.
 */
class CategoryCatalogService
{
    public function __construct(
        private readonly CategoryMenuService $categoryMenu,
        private readonly ProductPresentationService $productPresentation
    ) {
    }

    /**
     * Lấy dữ liệu hiển thị cho trang danh mục theo slug.
     *
     * @return array{
     *     category: object,
     *     parentCategory: ?object,
     *     menuCategories: \Illuminate\Support\Collection<int, object>,
     *     products: LengthAwarePaginator
     * }|null
     */
    public function getCategoryPageData(string $slug, int $perPage = 12): ?array
    {
        $category = DB::table('categories')
            ->select(['MaDanhMuc', 'MaDanhMucCha', 'TenDanhMuc', 'DuongDan', 'MoTa'])
            ->where('DuongDan', $slug)
            ->where('TrangThai', 'active')
            ->whereNull('NgayXoa')
            ->first();

        if ($category === null) {
            return null;
        }

        $parentCategory = null;
        if ($category->MaDanhMucCha !== null) {
            $parentCategory = DB::table('categories')
                ->select(['MaDanhMuc', 'TenDanhMuc', 'DuongDan'])
                ->where('MaDanhMuc', $category->MaDanhMucCha)
                ->where('TrangThai', 'active')
                ->whereNull('NgayXoa')
                ->first();
        }

        $childIds = DB::table('categories')
            ->where('MaDanhMucCha', $category->MaDanhMuc)
            ->where('TrangThai', 'active')
            ->whereNull('NgayXoa')
            ->pluck('MaDanhMuc')
            ->all();

        $targetCategoryIds = array_merge([$category->MaDanhMuc], $childIds);

        $products = $this->productPresentation->basePublicProductQuery()
            ->whereIn('products.MaDanhMuc', $targetCategoryIds)
            ->orderByDesc('products.NgayDang')
            ->orderByDesc('products.MaSanPham')
            ->paginate($perPage);

        $products->setCollection(
            $this->productPresentation->enrichProducts($products->getCollection())
        );

        return [
            'category' => $category,
            'parentCategory' => $parentCategory,
            'menuCategories' => $this->categoryMenu->getActiveMenuTree(),
            'products' => $products,
        ];
    }
}
