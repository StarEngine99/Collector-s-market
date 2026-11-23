<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CouponRequest;
use App\Services\CouponManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller quản lý Mã giảm giá phía quản trị (B5 - bảng `coupons`).
 */
class CouponController extends Controller
{
    public function __construct(
        private readonly CouponManagementService $couponService
    ) {
    }

    public function index(Request $request): View
    {
        $coupons = $this->couponService->paginateCoupons(
            $request->query('q'),
            $request->query('status'),
            $request->query('type'),
            15
        );

        return view('admin.coupons.index', [
            'coupons' => $coupons,
            'keyword' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
            'type' => (string) $request->query('type', ''),
        ]);
    }

    public function create(): View
    {
        return view('admin.coupons.create');
    }

    public function store(CouponRequest $request): RedirectResponse
    {
        $this->couponService->create($request->validated());

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Đã tạo mã giảm giá mới thành công.');
    }

    public function edit(int $id): View
    {
        $coupon = $this->couponService->findById($id);
        abort_if($coupon === null, 404);

        return view('admin.coupons.edit', ['coupon' => $coupon]);
    }

    public function update(CouponRequest $request, int $id): RedirectResponse
    {
        $coupon = $this->couponService->findById($id);
        abort_if($coupon === null, 404);

        $this->couponService->update($id, $request->validated());

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', 'Đã cập nhật mã giảm giá.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $result = $this->couponService->delete($id);
        abort_if($result['status'] === 'not_found', 404);

        return redirect()
            ->route('admin.coupons.index')
            ->with('success', $result['message']);
    }
}

