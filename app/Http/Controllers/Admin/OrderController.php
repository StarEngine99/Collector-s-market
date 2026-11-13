<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\OrderPaymentUpdateRequest;
use App\Http\Requests\Admin\OrderStatusUpdateRequest;
use App\Services\OrderManagementService;
use App\Support\OrderStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller quản lý Đơn hàng phía quản trị (B3):
 * danh sách, lọc trạng thái, xem/in chi tiết, cập nhật trạng thái đơn và xác nhận thanh toán.
 */
class OrderController extends Controller
{
    public function __construct(
        private readonly OrderManagementService $orderService
    ) {
    }

    /**
     * Danh sách đơn hàng kèm bộ lọc trạng thái đơn và trạng thái thanh toán.
     */
    public function index(Request $request): View
    {
        $orders = $this->orderService->paginateOrders(
            $request->query('q'),
            $request->query('status'),
            $request->query('payment_status'),
            15
        );

        return view('admin.orders.index', [
            'orders' => $orders,
            'keyword' => (string) $request->query('q', ''),
            'status' => (string) $request->query('status', ''),
            'paymentStatus' => (string) $request->query('payment_status', ''),
            'statusLabels' => OrderStatus::labels(),
        ]);
    }

    /**
     * Xem chi tiết đơn hàng.
     */
    public function show(int $id): View
    {
        $detail = $this->orderService->getOrderDetailById($id);
        abort_if($detail === null, 404);

        return view('admin.orders.show', [
            'order' => $detail['order'],
            'items' => $detail['items'],
            'payment' => $detail['payment'],
            'coupon' => $detail['coupon'],
            'customer' => $detail['customer'],
            'statusLabels' => OrderStatus::labels(),
        ]);
    }

    /**
     * Bản in phiếu giao hàng / chi tiết đơn hàng.
     */
    public function print(int $id): View
    {
        $detail = $this->orderService->getOrderDetailById($id);
        abort_if($detail === null, 404);

        return view('admin.orders.print', [
            'order' => $detail['order'],
            'items' => $detail['items'],
            'payment' => $detail['payment'],
            'coupon' => $detail['coupon'],
            'customer' => $detail['customer'],
        ]);
    }

    /**
     * Cập nhật trạng thái đơn hàng.
     */
    public function updateStatus(OrderStatusUpdateRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();

        $result = $this->orderService->updateOrderStatus(
            $id,
            (string) $validated['TrangThai'],
            $request->user()?->getKey() !== null ? (int) $request->user()->getKey() : null,
            $request->ip(),
            $validated['ly_do_huy'] ?? null
        );

        abort_if($result['status'] === 'not_found', 404);

        if ($result['status'] !== 'ok') {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }

    /**
     * Cập nhật / xác nhận trạng thái thanh toán của đơn hàng (Chưa thanh toán / Đã thanh toán).
     */
    public function updatePaymentStatus(OrderPaymentUpdateRequest $request, int $id): RedirectResponse
    {
        $validated = $request->validated();

        $result = $this->orderService->updatePaymentStatus(
            $id,
            (string) $validated['TrangThaiThanhToan'],
            $validated['MaGiaoDichNhaCungCap'] ?? null,
            $request->user()?->getKey() !== null ? (int) $request->user()->getKey() : null,
            $request->ip()
        );

        abort_if($result['status'] === 'not_found', 404);

        if ($result['status'] !== 'ok') {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}
