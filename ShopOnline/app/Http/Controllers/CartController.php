<?php

namespace App\Http\Controllers;

use App\Http\Requests\CartAddRequest;
use App\Http\Requests\CartCouponRequest;
use App\Http\Requests\CartUpdateRequest;
use App\Services\CartService;
use App\Services\CouponManagementService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Controller xử lý Giỏ hàng (A5): xem giỏ, thêm/sửa/xóa sản phẩm và áp/gỡ mã giảm giá.
 */
class CartController extends Controller
{
    private const COUPON_SESSION_KEY = 'applied_coupon_code';
    private const SHIPPING_COUPON_SESSION_KEY = 'applied_shipping_coupon_code';

    public function __construct(
        private readonly CartService $cartService,
        private readonly CouponManagementService $couponService
    ) {
    }

    /**
     * Hiển thị trang Giỏ hàng.
     */
    public function index(Request $request): View
    {
        $userId = $request->user()?->getKey() !== null ? (int) $request->user()->getKey() : null;
        $sessionKey = $request->session()->getId();
        $couponCode = $request->session()->get(self::COUPON_SESSION_KEY);
        $shippingCouponCode = $request->session()->get(self::SHIPPING_COUPON_SESSION_KEY);

        $summary = $this->cartService->getCartSummary(
            $userId,
            $sessionKey,
            is_string($couponCode) ? $couponCode : null,
            is_string($shippingCouponCode) ? $shippingCouponCode : null
        );

        $savedCoupons = collect();
        if ($userId !== null) {
            $savedCoupons = DB::table('user_coupons')
                ->join('coupons', 'coupons.MaPhieuGiamGia', '=', 'user_coupons.MaPhieuGiamGia')
                ->select([
                    'coupons.MaPhieuGiamGia',
                    'coupons.MaUuDai',
                    'coupons.LoaiPhieu',
                    'coupons.LoaiGiamGia',
                    'coupons.GiaTriGiam',
                    'coupons.DonHangToiThieu',
                    'coupons.BatDauLuc',
                    'coupons.KetThucLuc',
                ])
                ->where('user_coupons.MaNguoiDung', $userId)
                ->where('coupons.TrangThai', 'active')
                ->orderByDesc('user_coupons.NgayLuu')
                ->get();
        }

        return view('cart.index', [
            'summary' => $summary,
            'appliedCouponCode' => is_string($couponCode) ? $couponCode : '',
            'appliedShippingCouponCode' => is_string($shippingCouponCode) ? $shippingCouponCode : '',
            'savedCoupons' => $savedCoupons,
        ]);
    }

    /**
     * Thêm sản phẩm vào giỏ hàng (nhận từ trang chi tiết sản phẩm của TV2).
     */
    public function add(CartAddRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $userId = $request->user()?->getKey() !== null ? (int) $request->user()->getKey() : null;
        $sessionKey = $request->session()->getId();

        $result = $this->cartService->addItem(
            $userId,
            $sessionKey,
            (int) $validated['MaSanPham'],
            (int) $validated['SoLuong']
        );

        if ($result['status'] !== 'ok') {
            return back()->with('error', $result['message']);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', $result['message']);
    }

    /**
     * Cập nhật số lượng sản phẩm trong giỏ hàng.
     */
    public function update(CartUpdateRequest $request, int $itemId): RedirectResponse
    {
        $userId = $request->user()?->getKey() !== null ? (int) $request->user()->getKey() : null;
        $sessionKey = $request->session()->getId();

        $result = $this->cartService->updateItemQuantity(
            $userId,
            $sessionKey,
            $itemId,
            (int) $request->validated('SoLuong')
        );

        if (!in_array($result['status'], ['ok', 'capped'], true)) {
            return redirect()
                ->route('cart.index')
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', $result['message']);
    }

    /**
     * Xóa 1 sản phẩm khỏi giỏ hàng.
     */
    public function remove(Request $request, int $itemId): RedirectResponse
    {
        $userId = $request->user()?->getKey() !== null ? (int) $request->user()->getKey() : null;
        $sessionKey = $request->session()->getId();

        $removed = $this->cartService->removeItem($userId, $sessionKey, $itemId);

        if (!$removed) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Không tìm thấy sản phẩm trong giỏ hàng.');
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Đã xóa sản phẩm khỏi giỏ hàng.');
    }

    /**
     * Xóa toàn bộ sản phẩm trong giỏ hàng.
     */
    public function clear(Request $request): RedirectResponse
    {
        $userId = $request->user()?->getKey() !== null ? (int) $request->user()->getKey() : null;
        $sessionKey = $request->session()->getId();

        $this->cartService->clearCart($userId, $sessionKey);
        $request->session()->forget(self::COUPON_SESSION_KEY);
        $request->session()->forget(self::SHIPPING_COUPON_SESSION_KEY);

        return redirect()
            ->route('cart.index')
            ->with('success', 'Đã làm trống giỏ hàng.');
    }

    /**
     * Áp dụng mã giảm giá vào giỏ hàng (tối đa 1 mã mỗi đơn).
     */
    public function applyCoupon(CartCouponRequest $request): RedirectResponse
    {
        $code = (string) $request->validated('MaUuDai');
        $voucherType = $request->input('LoaiPhieu', 'san_pham');
        $voucherType = in_array($voucherType, ['san_pham', 'van_chuyen'], true) ? $voucherType : 'san_pham';
        $userId = $request->user()?->getKey() !== null ? (int) $request->user()->getKey() : null;
        $sessionKey = $request->session()->getId();

        $summary = $this->cartService->getCartSummary($userId, $sessionKey, null);
        if ($summary['items']->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng đang trống, không thể áp dụng mã giảm giá.');
        }

        $basis = $voucherType === 'van_chuyen' ? 30000.0 : (float) $summary['subtotal'];
        $check = $this->couponService->validateAndCalculateDiscount(
            $code,
            $basis,
            $userId,
            $voucherType
        );

        if (!$check['valid']) {
            return redirect()
                ->route('cart.index')
                ->with('error', $check['message']);
        }

        if ($voucherType === 'van_chuyen') {
            $request->session()->put(self::SHIPPING_COUPON_SESSION_KEY, mb_strtoupper(trim($code)));
        } else {
            $request->session()->put(self::COUPON_SESSION_KEY, mb_strtoupper(trim($code)));
        }

        return redirect()
            ->route('cart.index')
            ->with('success', $check['message']);
    }

    /**
     * Gỡ mã giảm giá đang áp dụng.
     */
    public function removeCoupon(Request $request): RedirectResponse
    {
        if ($request->input('LoaiPhieu') === 'van_chuyen') {
            $request->session()->forget(self::SHIPPING_COUPON_SESSION_KEY);
        } else {
            $request->session()->forget(self::COUPON_SESSION_KEY);
        }

        return redirect()
            ->route('cart.index')
            ->with('success', 'Đã gỡ mã giảm giá.');
    }
}
