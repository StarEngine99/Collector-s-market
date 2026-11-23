<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\ChangePasswordRequest;
use App\Http\Requests\Account\UpdateProfileRequest;
use App\Services\AccountAddressService;
use App\Services\AccountService;
use App\Services\OrderManagementService;
use App\Support\OrderStatus;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controller xử lý Tài khoản cá nhân & Lịch sử đơn hàng (A7).
 */
class AccountController extends Controller
{
    public function __construct(
        private readonly AccountAddressService $addressService,
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
        $userId = (int) $user->getKey();

        $favoriteProducts = DB::table('wishlists')
            ->join('products', 'products.MaSanPham', '=', 'wishlists.MaSanPham')
            ->select([
                'products.MaSanPham',
                'products.TenSanPham',
                'products.DuongDan',
                'products.Gia',
                DB::raw('(SELECT DuongDanHinhAnh FROM product_images WHERE product_images.MaSanPham = products.MaSanPham ORDER BY ThuTuSapXep ASC, MaHinhAnh ASC LIMIT 1) as HinhAnh'),
            ])
            ->where('wishlists.MaNguoiDung', $userId)
            ->whereNull('products.NgayXoa')
            ->limit(12)
            ->get();

        $purchasedProducts = DB::table('order_items')
            ->join('orders', 'orders.MaDonHang', '=', 'order_items.MaDonHang')
            ->join('products', 'products.MaSanPham', '=', 'order_items.MaSanPham')
            ->select([
                'products.MaSanPham',
                'products.TenSanPham',
                'products.DuongDan',
                'products.Gia',
                DB::raw('SUM(order_items.SoLuong) as SoLuongDaMua'),
                DB::raw('(SELECT DuongDanHinhAnh FROM product_images WHERE product_images.MaSanPham = products.MaSanPham ORDER BY ThuTuSapXep ASC, MaHinhAnh ASC LIMIT 1) as HinhAnh'),
            ])
            ->where('orders.MaNguoiDung', $userId)
            ->where('orders.TrangThai', 'completed')
            ->groupBy('products.MaSanPham', 'products.TenSanPham', 'products.DuongDan', 'products.Gia')
            ->limit(12)
            ->get();

        $ownedCoupons = DB::table('user_coupons')
            ->join('coupons', 'coupons.MaPhieuGiamGia', '=', 'user_coupons.MaPhieuGiamGia')
            ->where('user_coupons.MaNguoiDung', $userId)
            ->where('coupons.TrangThai', 'active')
            ->orderByDesc('user_coupons.NgayLuu')
            ->limit(12)
            ->get();

        $reviewedProducts = DB::table('product_reviews')
            ->join('products', 'products.MaSanPham', '=', 'product_reviews.MaSanPham')
            ->select([
                'products.MaSanPham',
                'products.TenSanPham',
                'products.DuongDan',
                'product_reviews.SoSao',
                'product_reviews.NoiDung',
                'product_reviews.NgayCapNhat',
                DB::raw('(SELECT DuongDanHinhAnh FROM product_images WHERE product_images.MaSanPham = products.MaSanPham ORDER BY ThuTuSapXep ASC, MaHinhAnh ASC LIMIT 1) as HinhAnh'),
            ])
            ->where('product_reviews.MaNguoiDung', $userId)
            ->orderByDesc('product_reviews.NgayCapNhat')
            ->limit(12)
            ->get();

        return view('account.index', [
            'user' => $user,
            'addresses' => $this->addressService->listForUser((int) $user->getKey()),
            'recentOrders' => $recentOrders,
            'favoriteProducts' => $favoriteProducts,
            'purchasedProducts' => $purchasedProducts,
            'ownedCoupons' => $ownedCoupons,
            'reviewedProducts' => $reviewedProducts,
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

