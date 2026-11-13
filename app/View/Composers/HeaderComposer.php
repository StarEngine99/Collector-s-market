<?php

namespace App\View\Composers;

use App\Services\CategoryMenuService;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Throwable;

/**
 * Chia sẻ danh sách danh mục công khai cho partial header.
 */
class HeaderComposer
{
    public function __construct(
        private readonly CategoryMenuService $categoryMenuService
    ) {
    }

    /**
     * Gắn biến $menuCategories vào view header.
     */
    public function compose(View $view): void
    {
        try {
            $menuCategories = $this->categoryMenuService->getActiveMenuTree();
        } catch (Throwable) {
            // Tránh làm hỏng trang tĩnh nếu DB chưa sẵn sàng hoặc đang chạy kiểm thử giao diện.
            $menuCategories = new Collection();
        }

        $view->with('menuCategories', $menuCategories);
    }
}
