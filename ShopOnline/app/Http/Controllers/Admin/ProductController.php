<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ProductRequest;
use App\Services\ProductManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductManagementService $productService
    ) {
    }

    public function index(Request $request): View
    {
        $keyword = trim((string) $request->query('keyword', ''));
        $categoryId = is_numeric($request->query('category_id'))
            ? (int) $request->query('category_id')
            : null;
        $status = in_array($request->query('status'), ['draft', 'active', 'hidden'], true)
            ? (string) $request->query('status')
            : null;
        $stock = in_array($request->query('stock'), ['in_stock', 'out_of_stock'], true)
            ? (string) $request->query('stock')
            : null;

        return view('admin.products.index', [
            'products' => $this->productService->paginateProducts($keyword, $categoryId, $status, $stock),
            'categories' => $this->productService->categories(),
            'filters' => [
                'keyword' => $keyword,
                'category_id' => $categoryId,
                'status' => $status,
                'stock' => $stock,
            ],
        ]);
    }

    public function create(): View
    {
        return view('admin.products.create', [
            'product' => null,
            'images' => collect(),
            'categories' => $this->productService->categories(),
        ]);
    }

    public function store(ProductRequest $request): RedirectResponse
    {
        $this->productService->create(
            $request->validated(),
            $request->file('HinhAnh', []),
            $request->user() ? (int) $request->user()->getKey() : null
        );

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Đã tạo sản phẩm.');
    }

    public function edit(int $id): View
    {
        $detail = $this->productService->findProduct($id);
        abort_if($detail === null, 404);

        return view('admin.products.edit', [
            'product' => $detail['product'],
            'images' => $detail['images'],
            'categories' => $this->productService->categories(),
        ]);
    }

    public function update(ProductRequest $request, int $id): RedirectResponse
    {
        $updated = $this->productService->update(
            $id,
            $request->validated(),
            $request->file('HinhAnh', []),
            $request->user() ? (int) $request->user()->getKey() : null
        );
        abort_if(!$updated, 404);

        return redirect()
            ->route('admin.products.edit', $id)
            ->with('success', 'Đã cập nhật sản phẩm.');
    }

    public function destroy(int $id): RedirectResponse
    {
        abort_if(!$this->productService->softDelete($id), 404);

        return redirect()
            ->route('admin.products.index')
            ->with('success', 'Đã ẩn sản phẩm.');
    }
}
