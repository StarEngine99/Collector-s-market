<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\ChangePasswordRequest;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Services\AccountService;
use App\Services\OrderManagementService;
use App\Support\OrderStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller xử lý Tài khoản cá nhân & Lịch sử đơn hàng (A7).
 */
class AccountController extends Controller
{
    public function __construct(
        private readonly AccountService $accountService,
        private readonly OrderManagementService $orderManagementService
    ) {
    }

    /**
     * Trang tổng quan tài khoản cá nhân (thông tin hồ sơ, đổi mật khẩu & đơn hàng gần nhất).
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        abort_if($user === null || $user->isLocked(), 403);

        $recentOrders = $this->accountService->paginateCustomerOrders((int) $user->getKey(), null, 5);

        return view('account.index', [
            'user' => $user,
            'recentOrders' => $recentOrders,
        ]);
    }

    /**
     * Cập nhật họ tên và số điện thoại cá nhân.
     */
    public function updateProfile(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null || $user->isLocked(), 403);

        $this->accountService->updateProfile((int) $user->getKey(), $request->validated());

        return redirect()
            ->route('account.index')
            ->with('success', 'Đã cập nhật thông tin tài khoản thành công.');
    }

    /**
     * Đổi mật khẩu tài khoản.
     */
    public function changePassword(ChangePasswordRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null || $user->isLocked(), 403);

        $validated = $request->validated();
        $result = $this->accountService->changePassword(
            (int) $user->getKey(),
            (string) $validated['MatKhauHienTai'],
            (string) $validated['MatKhau']
        );

        if ($result['status'] !== 'ok') {
            return back()->withErrors(['MatKhauHienTai' => $result['message']]);
        }

        return redirect()
            ->route('account.index')
            ->with('success', $result['message']);
    }

    /**
     * Danh sách toàn bộ đơn hàng của khách hàng, có lọc theo trạng thái.
     */
    public function orders(Request $request): View
    {
        $user = $request->user();
        abort_if($user === null || $user->isLocked(), 403);

        $status = (string) $request->query('status', '');
        $orders = $this->accountService->paginateCustomerOrders(
            (int) $user->getKey(),
            $status !== '' ? $status : null,
            10
        );

        return view('account.orders.index', [
            'orders' => $orders,
            'status' => $status,
            'statusLabels' => OrderStatus::labels(),
        ]);
    }

    /**
     * Chi tiết một đơn hàng thuộc sở hữu của khách hàng.
     */
    public function showOrder(Request $request, string $orderNumber): View
    {
        $user = $request->user();
        abort_if($user === null || $user->isLocked(), 403);

        $detail = $this->accountService->getCustomerOrderDetail($orderNumber, (int) $user->getKey());
        abort_if($detail === null, 404);

        return view('account.orders.show', [
            'order' => $detail['order'],
            'items' => $detail['items'],
            'payment' => $detail['payment'],
            'coupon' => $detail['coupon'],
            'bankTransferConfig' => config('storefront.bank_transfer', []),
        ]);
    }

    /**
     * Khách hàng hủy đơn hàng (chỉ cho phép ở trạng thái "Đang chuẩn bị hàng" hoặc "Đã chuẩn bị hàng").
     */
    public function cancelOrder(Request $request, string $orderNumber): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null || $user->isLocked(), 403);

        $result = $this->orderManagementService->cancelOrderByCustomer(
            $orderNumber,
            (int) $user->getKey()
        );

        abort_if($result['status'] === 'not_found', 404);

        if ($result['status'] !== 'ok') {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}

