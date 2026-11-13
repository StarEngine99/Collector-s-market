<?php

namespace App\Http\Controllers;

use App\Services\HomeService;
use Illuminate\View\View;

/**
 * Controller trang chủ (A1).
 */
class HomeController extends Controller
{
    public function __construct(
        private readonly HomeService $homeService
    ) {
    }

    /**
     * Hiển thị trang chủ gồm banner trượt, sản phẩm nổi bật và sản phẩm mới.
     */
    public function index(): View
    {
        return view('home.index', [
            'banners' => $this->homeService->getActiveBanners(),
            'featuredProducts' => $this->homeService->getFeaturedProducts(8),
            'newProducts' => $this->homeService->getNewProducts(8),
        ]);
    }
}
