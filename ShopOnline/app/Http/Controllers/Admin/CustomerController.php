<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\CustomerAccountRequest;
use App\Services\CustomerManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Controller Quản lý khách hàng phía Quản trị (B4):
 * - Danh sách khách hàng, lọc theo từ khóa / trạng thái
 * - Xem chi tiết khách hàng & lịch sử mua hàng
 * - Tạo, sửa, ẩn mềm và khóa / mở khóa tài khoản khách hàng theo quyền
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

    public function create(): View
    {
        return view('admin.customers.form', [
            'customer' => null,
            'formAction' => route('admin.customers.store'),
            'formMethod' => 'POST',
            'heading' => 'Tạo tài khoản khách hàng',
        ]);
    }

    public function store(CustomerAccountRequest $request): RedirectResponse
    {
        $customerId = $this->customerService->createCustomer(
            $request->validated(),
            (int) $request->user()->getKey(),
            $request->ip()
        );

        return redirect()
            ->route('admin.customers.show', $customerId)
            ->with('success', 'Đã tạo tài khoản khách hàng.');
    }

    public function edit(int $id): View
    {
        $customer = $this->customerService->findCustomer($id);
        abort_if($customer === null, 404);

        return view('admin.customers.form', [
            'customer' => $customer,
            'formAction' => route('admin.customers.update', $id),
            'formMethod' => 'PUT',
            'heading' => 'Cập nhật tài khoản khách hàng',
        ]);
    }

    public function update(CustomerAccountRequest $request, int $id): RedirectResponse
    {
        $updated = $this->customerService->updateCustomer(
            $id,
            $request->validated(),
            (int) $request->user()->getKey(),
            $request->ip()
        );
        abort_if(! $updated, 404);

        return redirect()
            ->route('admin.customers.show', $id)
            ->with('success', 'Đã cập nhật tài khoản khách hàng.');
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $deleted = $this->customerService->deleteCustomer(
            $id,
            (int) $request->user()->getKey(),
            $request->ip()
        );
        abort_if(! $deleted, 404);

        return redirect()
            ->route('admin.customers.index')
            ->with('success', 'Đã ẩn tài khoản khách hàng và giữ nguyên lịch sử đơn hàng.');
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

