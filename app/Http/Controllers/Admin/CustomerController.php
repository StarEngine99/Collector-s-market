<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\CustomerManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Controller Quản lý khách hàng phía Quản trị (B4):
 * - Danh sách khách hàng, lọc theo từ khóa / trạng thái
 * - Xem chi tiết khách hàng & lịch sử mua hàng
 * - Khóa / mở khóa tài khoản khách hàng
 */
class CustomerController extends Controller
{
    public function __construct(
        private readonly CustomerManagementService $customerService
    ) {
    }

    /**
     * Danh sách khách hàng kèm bộ lọc.
     */
    public function index(Request $request): View
    {
        $keyword = (string) $request->query('keyword', '');
        $status = (string) $request->query('status', '');

        $customers = $this->customerService->paginateCustomers(
            $keyword !== '' ? $keyword : null,
            $status !== '' ? $status : null,
            15
        );

        return view('admin.customers.index', [
            'customers' => $customers,
            'keyword' => $keyword,
            'status' => $status,
        ]);
    }

    /**
     * Xem chi tiết thông tin khách hàng và lịch sử đơn hàng đã mua.
     */
    public function show(int $id): View
    {
        $detail = $this->customerService->getCustomerDetail($id);
        abort_if($detail === null, 404);

        return view('admin.customers.show', [
            'customer' => $detail['customer'],
            'addresses' => $detail['addresses'],
            'orders' => $detail['orders'],
            'stats' => $detail['stats'],
        ]);
    }

    /**
     * Khóa hoặc mở khóa tài khoản khách hàng.
     */
    public function updateStatus(Request $request, int $id): RedirectResponse
    {
        $validated = $request->validate([
            'TrangThai' => ['required', 'string', Rule::in(['active', 'locked'])],
        ], [
            'TrangThai.required' => 'Vui lòng chọn trạng thái tài khoản.',
            'TrangThai.in' => 'Trạng thái tài khoản không hợp lệ.',
        ]);

        $adminId = $request->user() ? (int) $request->user()->getKey() : null;

        $result = $this->customerService->updateCustomerStatus(
            $id,
            (string) $validated['TrangThai'],
            $adminId,
            $request->ip()
        );

        abort_if($result['status'] === 'not_found', 404);

        if (! in_array($result['status'], ['ok', 'unchanged'], true)) {
            return back()->with('error', $result['message']);
        }

        return back()->with('success', $result['message']);
    }
}

