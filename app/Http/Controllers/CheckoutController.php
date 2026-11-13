<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutRequest;
use App\Services\CartService;
use App\Services\CheckoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Controller xử lý Thanh toán và Đặt hàng (A5).
 * Yêu cầu khách hàng đã đăng nhập (`auth`) mới được thanh toán và tạo đơn.
 */
class CheckoutController extends Controller
{
    private const COUPON_SESSION_KEY = 'applied_coupon_code';

    public function __construct(
        private readonly CartService $cartService,
        private readonly CheckoutService $checkoutService
    ) {
    }

    /**
     * Hiển thị trang Thanh toán (nhập địa chỉ giao hàng & chọn COD / Chuyển khoản).
     */
    public function index(Request $request): View|RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null || $user->isLocked(), 403);

        $userId = (int) $user->getKey();
        $sessionKey = $request->session()->getId();
        $couponCode = $request->session()->get(self::COUPON_SESSION_KEY);

        $summary = $this->cartService->getCartSummary(
            $userId,
            $sessionKey,
            is_string($couponCode) ? $couponCode : null
        );

        if ($summary['items']->isEmpty()) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Giỏ hàng của bạn đang trống. Vui lòng chọn sản phẩm trước khi thanh toán.');
        }

        if ($summary['has_invalid_items']) {
            return redirect()
                ->route('cart.index')
                ->with('error', 'Một số sản phẩm trong giỏ vượt quá tồn kho hoặc đã hết hàng. Vui lòng cập nhật lại giỏ hàng.');
        }

        $addresses = $this->checkoutService->getUserAddresses($userId);
        $defaultAddress = $addresses->firstWhere('MacDinh', 1) ?? $addresses->first();

        return view('checkout.index', [
            'user' => $user,
            'summary' => $summary,
            'addresses' => $addresses,
            'defaultAddress' => $defaultAddress,
            'bankTransferConfig' => config('storefront.bank_transfer', []),
        ]);
    }

    /**
     * Xử lý tạo đơn hàng (COD hoặc Chuyển khoản).
     */
    public function store(CheckoutRequest $request): RedirectResponse
    {
        $user = $request->user();
        abort_if($user === null || $user->isLocked(), 403);

        $userId = (int) $user->getKey();
        $sessionKey = $request->session()->getId();
        $couponCode = $request->session()->get(self::COUPON_SESSION_KEY);

        $result = $this->checkoutService->placeOrder(
            $userId,
            $sessionKey,
            $request->validated(),
            is_string($couponCode) ? $couponCode : null
        );

        if ($result['status'] !== 'ok' || empty($result['order'])) {
            return back()
                ->withInput()
                ->with('error', $result['message']);
        }

        $request->session()->forget(self::COUPON_SESSION_KEY);

        return redirect()
            ->route('checkout.success', ['orderNumber' => $result['order']->SoDonHang])
            ->with('success', $result['message']);
    }

    /**
     * Hiển thị trang Đặt hàng thành công kèm hướng dẫn chuyển khoản (nếu chọn Chuyển khoản).
     */
    public function success(Request $request, string $orderNumber): View
    {
        $user = $request->user();
        abort_if($user === null, 403);

        $orderData = $this->checkoutService->getOrderForCustomer($orderNumber, (int) $user->getKey());
        abort_if($orderData === null, 404);

        return view('checkout.success', [
            'order' => $orderData['order'],
            'items' => $orderData['items'],
            'payment' => $orderData['payment'],
            'coupon' => $orderData['coupon'],
            'bankTransferConfig' => config('storefront.bank_transfer', []),
        ]);
    }
}
