<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\BannerRequest;
use App\Services\BannerManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller quản lý banner trang chủ và thứ tự hiển thị (B6).
 */
class BannerController extends Controller
{
    public function __construct(
        private readonly BannerManagementService $bannerService
    ) {
    }

    public function index(): View
    {
        return view('admin.banners.index', [
            'banners' => $this->bannerService->paginateBanners(15),
        ]);
    }

    public function create(): View
    {
        return view('admin.banners.create');
    }

    public function store(BannerRequest $request): RedirectResponse
    {
        $this->bannerService->create(
            $request->validated(),
            $request->file('hinh_anh'),
            $request->user()?->getKey()
        );

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Đã thêm banner mới thành công.');
    }

    public function edit(int $id): View
    {
        $banner = $this->bannerService->findById($id);
        abort_if($banner === null, 404);

        return view('admin.banners.edit', ['banner' => $banner]);
    }

    public function update(BannerRequest $request, int $id): RedirectResponse
    {
        $updated = $this->bannerService->update(
            $id,
            $request->validated(),
            $request->file('hinh_anh')
        );
        abort_if(!$updated, 404);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Đã cập nhật banner.');
    }

    public function updateOrder(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'ThuTuSapXep' => ['required', 'integer', 'min:0', 'max:9999'],
        ]);

        $updated = $this->bannerService->updateOrder($id, (int) $validated['ThuTuSapXep']);
        abort_if(!$updated, 404);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Đã cập nhật thứ tự hiển thị banner.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $deleted = $this->bannerService->delete($id);
        abort_if(!$deleted, 404);

        return redirect()
            ->route('admin.banners.index')
            ->with('success', 'Đã xóa banner.');
    }
}
