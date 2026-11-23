<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PromotionRequest;
use App\Services\PromotionManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller quản lý Chương trình khuyến mãi theo sản phẩm phía quản trị (B5 - bảng `promotions`).
 */
class PromotionController extends Controller
{
    public function __construct(
        private readonly PromotionManagementService $promotionService
    ) {
    }

    public function index(Request $request): View
    {
        $promotions = $this->promotionService->paginatePromotions(
            $request->query('q'),
            $request->query('status'),
            15
        );

        return view('admin.promotions.index', [
            'promotions' => $promotions,
            'keyword' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
        ]);
    }

    public function create(): View
    {
        return view('admin.promotions.create', [
            'products' => $this->promotionService->getSelectableProducts(),
            'selectedProductIds' => [],
        ]);
    }

    public function store(PromotionRequest $request): RedirectResponse
    {
        $this->promotionService->create($request->validated());

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã tạo chương trình khuyến mãi theo sản phẩm thành công.');
    }

    public function edit(int $id): View
    {
        $promotion = $this->promotionService->findById($id);
        abort_if($promotion === null, 404);

        return view('admin.promotions.edit', [
            'promotion' => $promotion,
            'products' => $this->promotionService->getSelectableProducts(),
            'selectedProductIds' => $this->promotionService->getAssignedProductIds($id),
        ]);
    }

    public function update(PromotionRequest $request, int $id): RedirectResponse
    {
        $promotion = $this->promotionService->findById($id);
        abort_if($promotion === null, 404);

        $this->promotionService->update($id, $request->validated());

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã cập nhật chương trình khuyến mãi.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $deleted = $this->promotionService->delete($id);
        abort_if(!$deleted, 404);

        return redirect()
            ->route('admin.promotions.index')
            ->with('success', 'Đã xóa chương trình khuyến mãi.');
    }
}

