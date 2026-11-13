<?php

namespace App\Http\Controllers;

use App\Services\CategoryCatalogService;
use Illuminate\View\View;

/**
 * Controller trang danh mục sản phẩm (A2).
 */
class CategoryController extends Controller
{
    public function __construct(
        private readonly CategoryCatalogService $categoryCatalogService
    ) {
    }

    /**
     * Hiển thị danh sách sản phẩm thuộc một danh mục (có phân trang).
     */
    public function show(string $slug): View
    {
        $data = $this->categoryCatalogService->getCategoryPageData($slug, 12);

        abort_if($data === null, 404);

        return view('categories.show', $data);
    }
}
