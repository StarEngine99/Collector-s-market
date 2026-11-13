<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service tìm kiếm, lọc và sắp xếp sản phẩm công khai (A4).
 */
class ProductSearchService
{
    public function __construct(
        private readonly CategoryMenuService $categoryMenu,
        private readonly ProductPresentationService $productPresentation
    ) {
    }

    /**
     * Tìm kiếm & lọc sản phẩm theo từ khóa, danh mục, khoảng giá và kiểu sắp xếp.
     *
     * @param array<string, mixed> $filters
     * @return array{
     *     products: LengthAwarePaginator,
     *     categories: Collection<int, object>,
     *     filters: array<string, mixed>
     * }
     */
    public function search(array $filters, int $perPage = 12): array
    {
        $query = $this->productPresentation->basePublicProductQuery();

        $keyword = trim((string) ($filters['q'] ?? ''));
        if ($keyword !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $keyword);
            $query->where('products.TenSanPham', 'like', '%' . $escaped . '%');
        }

        $categorySlug = trim((string) ($filters['category'] ?? ''));
        if ($categorySlug !== '') {
            $categoryIds = $this->resolveCategoryTreeIds($categorySlug);
            if ($categoryIds === []) {
                $query->whereRaw('1 = 0');
            } else {
                $query->whereIn('products.MaDanhMuc', $categoryIds);
            }
        }

        $minPrice = $filters['min_price'] ?? null;
        if ($minPrice !== null && $minPrice !== '') {
            $query->where('products.Gia', '>=', (float) $minPrice);
        }

        $maxPrice = $filters['max_price'] ?? null;
        if ($maxPrice !== null && $maxPrice !== '') {
            $query->where('products.Gia', '<=', (float) $maxPrice);
        }

        $sort = (string) ($filters['sort'] ?? 'newest');
        match ($sort) {
            'price_asc' => $query->orderBy('products.Gia', 'asc')->orderByDesc('products.MaSanPham'),
            'price_desc' => $query->orderBy('products.Gia', 'desc')->orderByDesc('products.MaSanPham'),
            default => $query->orderByDesc('products.NgayDang')->orderByDesc('products.MaSanPham'),
        };

        $products = $query->paginate($perPage)->withQueryString();
        $products->setCollection(
            $this->productPresentation->enrichProducts($products->getCollection())
        );

        return [
            'products' => $products,
            'categories' => $this->categoryMenu->getActiveMenuTree(),
            'filters' => [
                'q' => $keyword,
                'category' => $categorySlug,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'sort' => $sort,
            ],
        ];
    }

    /**
     * Lấy ID danh mục và các danh mục con đang hoạt động theo slug.
     *
     * @return array<int, int>
     */
    private function resolveCategoryTreeIds(string $slug): array
    {
        $category = DB::table('categories')
            ->select(['MaDanhMuc'])
            ->where('DuongDan', $slug)
            ->where('TrangThai', 'active')
            ->whereNull('NgayXoa')
            ->first();

        if ($category === null) {
            return [];
        }

        $childIds = DB::table('categories')
            ->where('MaDanhMucCha', $category->MaDanhMuc)
            ->where('TrangThai', 'active')
            ->whereNull('NgayXoa')
            ->pluck('MaDanhMuc')
            ->map(fn ($id): int => (int) $id)
            ->all();

        return array_merge([(int) $category->MaDanhMuc], $childIds);
    }
}
