<?php

namespace App\Http\Controllers;

use App\Services\ProductDetailService;
use Illuminate\View\View;

/**
 * Controller trang chi tiết sản phẩm (A3).
 */
class ProductController extends Controller
{
    public function __construct(
        private readonly ProductDetailService $productDetailService
    ) {
    }

    /**
     * Hiển thị chi tiết sản phẩm theo slug.
     */
    public function show(string $slug): View
    {
        $data = $this->productDetailService->getProductDetail($slug, 4);

        abort_if($data === null, 404);

        return view('products.show', $data);
    }
}
