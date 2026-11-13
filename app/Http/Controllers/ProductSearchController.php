<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductSearchRequest;
use App\Services\ProductSearchService;
use Illuminate\View\View;

/**
 * Controller tìm kiếm và lọc sản phẩm (A4).
 */
class ProductSearchController extends Controller
{
    public function __construct(
        private readonly ProductSearchService $productSearchService
    ) {
    }

    /**
     * Hiển thị kết quả tìm kiếm và lọc sản phẩm.
     */
    public function index(ProductSearchRequest $request): View
    {
        $data = $this->productSearchService->search($request->validated(), 12);

        return view('products.search', $data);
    }
}
