<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Service xử lý Giỏ hàng (A5): lưu tạm theo phiên cho khách chưa đăng nhập,
 * lưu theo tài khoản cho khách đã đăng nhập, tính giá khuyến mãi sản phẩm và áp mã giảm giá.
 */
class CartService
{
    public function __construct(
        private readonly ProductPresentationService $productPresentation,
        private readonly CouponManagementService $couponService
    ) {
    }

    /**
     * Tìm hoặc khởi tạo giỏ hàng theo MaNguoiDung (nếu đã đăng nhập) hoặc KhoaPhien (nếu là khách).
     */
    public function resolveCart(?int $userId, string $sessionKey, bool $createIfMissing = false): ?object
    {
        if ($userId !== null) {
            $cart = DB::table('carts')->where('MaNguoiDung', $userId)->first();

            if ($cart === null && $createIfMissing) {
                $now = Carbon::now();
                $cartId = (int) DB::table('carts')->insertGetId([
                    'MaNguoiDung' => $userId,
                    'KhoaPhien' => null,
                    'NgayTao' => $now,
                    'NgayCapNhat' => $now,
                ]);

                $cart = DB::table('carts')->where('MaGioHang', $cartId)->first();
            }

            return $cart;
        }

        if ($sessionKey === '') {
            return null;
        }

        $cart = DB::table('carts')
            ->where('KhoaPhien', $sessionKey)
            ->whereNull('MaNguoiDung')
            ->first();

        if ($cart === null && $createIfMissing) {
            $now = Carbon::now();
            $cartId = (int) DB::table('carts')->insertGetId([
                'MaNguoiDung' => null,
                'KhoaPhien' => $sessionKey,
                'NgayTao' => $now,
                'NgayCapNhat' => $now,
            ]);

            $cart = DB::table('carts')->where('MaGioHang', $cartId)->first();
        }

        return $cart;
    }

    /**
     * Lấy chi tiết giỏ hàng, tính giá khuyến mãi sản phẩm trước, sau đó áp mã giảm giá lên tổng tạm tính.
     *
     * @return array{
     *     cart: ?object,
     *     items: Collection<int, object>,
     *     total_quantity: int,
     *     subtotal: float,
     *     discount_amount: float,
     *     shipping_fee: float,
     *     total: float,
     *     coupon: ?object,
     *     coupon_error: ?string,
     *     has_invalid_items: bool
     * }
     */
    public function getCartSummary(?int $userId, string $sessionKey, ?string $couponCode = null): array
    {
        $cart = $this->resolveCart($userId, $sessionKey, false);

        if ($cart === null) {
            return [
                'cart' => null,
                'items' => collect(),
                'total_quantity' => 0,
                'subtotal' => 0.0,
                'discount_amount' => 0.0,
                'shipping_fee' => 0.0,
                'total' => 0.0,
                'coupon' => null,
                'coupon_error' => null,
                'has_invalid_items' => false,
            ];
        }

        $rawItems = DB::table('cart_items')
            ->join('products', 'products.MaSanPham', '=', 'cart_items.MaSanPham')
            ->join('categories', 'categories.MaDanhMuc', '=', 'products.MaDanhMuc')
            ->select([
                'cart_items.MaChiTietGioHang',
                'cart_items.MaGioHang',
                'cart_items.MaSanPham',
                'cart_items.SoLuong',
                'products.MaDanhMuc',
                'products.MaSKU',
                'products.TenSanPham',
                'products.DuongDan',
                'products.Gia',
                'products.SoLuongTon',
                'products.TrangThai',
                'products.NgayXoa',
                'categories.TenDanhMuc',
                'categories.TrangThai as TrangThaiDanhMuc',
                'categories.NgayXoa as NgayXoaDanhMuc',
            ])
            ->where('cart_items.MaGioHang', $cart->MaGioHang)
            ->orderBy('cart_items.MaChiTietGioHang')
            ->get();

        $enrichedItems = $this->productPresentation->enrichProducts($rawItems);

        $subtotal = 0.0;
        $totalQuantity = 0;
        $hasInvalidItems = false;

        $items = $enrichedItems->map(function (object $item) use (&$subtotal, &$totalQuantity, &$hasInvalidItems): object {
            $unitPrice = (float) ($item->GiaSauGiam ?? $item->Gia);
            $qty = (int) $item->SoLuong;
            $stock = (int) $item->SoLuongTon;

            $isAvailable = $item->TrangThai === 'active'
                && $item->NgayXoa === null
                && $item->TrangThaiDanhMuc === 'active'
                && $item->NgayXoaDanhMuc === null
                && $stock > 0;

            $isEnoughStock = $isAvailable && $qty <= $stock;

            if (!$isEnoughStock) {
                $hasInvalidItems = true;
            }

            $lineTotal = round($unitPrice * $qty, 2);
            $subtotal += $lineTotal;
            $totalQuantity += $qty;

            $item->DonGia = $unitPrice;
            $item->ThanhTien = $lineTotal;
            $item->ConBan = $isAvailable;
            $item->DuTonKho = $isEnoughStock;

            return $item;
        });

        $subtotal = round($subtotal, 2);
        $discountAmount = 0.0;
        $appliedCoupon = null;
        $couponError = null;

        if ($couponCode !== null && trim($couponCode) !== '' && $subtotal > 0) {
            $couponResult = $this->couponService->validateAndCalculateDiscount($couponCode, $subtotal, $userId);
            if ($couponResult['valid']) {
                $discountAmount = (float) $couponResult['discount_amount'];
                $appliedCoupon = $couponResult['coupon'] ?? null;
            } else {
                $couponError = $couponResult['message'];
            }
        }

        $shippingFee = 0.0;
        $total = max(0.0, round($subtotal - $discountAmount + $shippingFee, 2));

        return [
            'cart' => $cart,
            'items' => $items,
            'total_quantity' => $totalQuantity,
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'shipping_fee' => $shippingFee,
            'total' => $total,
            'coupon' => $appliedCoupon,
            'coupon_error' => $couponError,
            'has_invalid_items' => $hasInvalidItems,
        ];
    }

    /**
     * Thêm sản phẩm vào giỏ hàng (kiểm tra trạng thái đang bán và tồn kho).
     *
     * @return array{status: string, message: string}
     */
    public function addItem(?int $userId, string $sessionKey, int $productId, int $quantity): array
    {
        $product = DB::table('products')
            ->join('categories', 'categories.MaDanhMuc', '=', 'products.MaDanhMuc')
            ->select([
                'products.MaSanPham',
                'products.TenSanPham',
                'products.SoLuongTon',
                'products.TrangThai',
                'products.NgayXoa',
                'categories.TrangThai as TrangThaiDanhMuc',
                'categories.NgayXoa as NgayXoaDanhMuc',
            ])
            ->where('products.MaSanPham', $productId)
            ->first();

        if (
            $product === null ||
            $product->NgayXoa !== null ||
            $product->TrangThai !== 'active' ||
            $product->NgayXoaDanhMuc !== null ||
            $product->TrangThaiDanhMuc !== 'active'
        ) {
            return [
                'status' => 'unavailable',
                'message' => 'Sản phẩm này hiện không còn kinh doanh.',
            ];
        }

        $maxStock = (int) $product->SoLuongTon;
        if ($maxStock <= 0) {
            return [
                'status' => 'out_of_stock',
                'message' => 'Sản phẩm "' . $product->TenSanPham . '" hiện đang tạm hết hàng.',
            ];
        }

        $cart = $this->resolveCart($userId, $sessionKey, true);
        if ($cart === null) {
            return [
                'status' => 'error',
                'message' => 'Không thể khởi tạo giỏ hàng.',
            ];
        }

        $now = Carbon::now();
        $existingItem = DB::table('cart_items')
            ->where('MaGioHang', $cart->MaGioHang)
            ->where('MaSanPham', $productId)
            ->first();

        $currentQty = $existingItem !== null ? (int) $existingItem->SoLuong : 0;
        $desiredQty = $currentQty + max(1, $quantity);

        if ($desiredQty > $maxStock) {
            $desiredQty = $maxStock;
            $cappedMessage = sprintf(
                'Kho chỉ còn %d sản phẩm "%s". Đã cập nhật số lượng tối đa trong giỏ.',
                $maxStock,
                $product->TenSanPham
            );
        }

        if ($existingItem !== null) {
            DB::table('cart_items')
                ->where('MaChiTietGioHang', $existingItem->MaChiTietGioHang)
                ->update([
                    'SoLuong' => $desiredQty,
                    'NgayCapNhat' => $now,
                ]);
        } else {
            DB::table('cart_items')->insert([
                'MaGioHang' => $cart->MaGioHang,
                'MaSanPham' => $productId,
                'SoLuong' => $desiredQty,
                'NgayTao' => $now,
                'NgayCapNhat' => $now,
            ]);
        }

        DB::table('carts')
            ->where('MaGioHang', $cart->MaGioHang)
            ->update(['NgayCapNhat' => $now]);

        return [
            'status' => 'ok',
            'message' => $cappedMessage ?? sprintf('Đã thêm "%s" vào giỏ hàng.', $product->TenSanPham),
        ];
    }

    /**
     * Cập nhật số lượng của một dòng sản phẩm trong giỏ.
     *
     * @return array{status: string, message: string}
     */
    public function updateItemQuantity(
        ?int $userId,
        string $sessionKey,
        int $cartItemId,
        int $quantity
    ): array {
        $cart = $this->resolveCart($userId, $sessionKey, false);
        if ($cart === null) {
            return [
                'status' => 'not_found',
                'message' => 'Không tìm thấy giỏ hàng.',
            ];
        }

        $item = DB::table('cart_items')
            ->join('products', 'products.MaSanPham', '=', 'cart_items.MaSanPham')
            ->select([
                'cart_items.MaChiTietGioHang',
                'cart_items.MaSanPham',
                'products.TenSanPham',
                'products.SoLuongTon',
                'products.TrangThai',
                'products.NgayXoa',
            ])
            ->where('cart_items.MaChiTietGioHang', $cartItemId)
            ->where('cart_items.MaGioHang', $cart->MaGioHang)
            ->first();

        if ($item === null) {
            return [
                'status' => 'not_found',
                'message' => 'Sản phẩm không có trong giỏ hàng của bạn.',
            ];
        }

        $maxStock = (int) $item->SoLuongTon;
        if ($item->NgayXoa !== null || $item->TrangThai !== 'active' || $maxStock <= 0) {
            return [
                'status' => 'out_of_stock',
                'message' => 'Sản phẩm "' . $item->TenSanPham . '" đã hết hàng. Vui lòng xóa khỏi giỏ.',
            ];
        }

        $finalQty = min($maxStock, max(1, $quantity));
        $now = Carbon::now();

        DB::table('cart_items')
            ->where('MaChiTietGioHang', $cartItemId)
            ->update([
                'SoLuong' => $finalQty,
                'NgayCapNhat' => $now,
            ]);

        DB::table('carts')
            ->where('MaGioHang', $cart->MaGioHang)
            ->update(['NgayCapNhat' => $now]);

        if ($quantity > $maxStock) {
            return [
                'status' => 'capped',
                'message' => sprintf(
                    'Sản phẩm "%s" chỉ còn %d sản phẩm trong kho. Đã điều chỉnh về số lượng tối đa.',
                    $item->TenSanPham,
                    $maxStock
                ),
            ];
        }

        return [
            'status' => 'ok',
            'message' => 'Đã cập nhật số lượng sản phẩm.',
        ];
    }

    /**
     * Xóa 1 sản phẩm khỏi giỏ hàng.
     */
    public function removeItem(?int $userId, string $sessionKey, int $cartItemId): bool
    {
        $cart = $this->resolveCart($userId, $sessionKey, false);
        if ($cart === null) {
            return false;
        }

        return DB::table('cart_items')
            ->where('MaChiTietGioHang', $cartItemId)
            ->where('MaGioHang', $cart->MaGioHang)
            ->delete() > 0;
    }

    /**
     * Xóa toàn bộ sản phẩm trong giỏ hàng.
     */
    public function clearCart(?int $userId, string $sessionKey): void
    {
        $cart = $this->resolveCart($userId, $sessionKey, false);
        if ($cart === null) {
            return;
        }

        DB::table('cart_items')
            ->where('MaGioHang', $cart->MaGioHang)
            ->delete();
    }
}
