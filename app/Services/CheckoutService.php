<?php

namespace App\Services;

use App\Support\OrderStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Service xử lý Đặt hàng và Thanh toán (A5):
 * - Tạo đơn hàng trong DB transaction (trạng thái đầu tiên: "Đang chuẩn bị hàng" / 'preparing').
 * - Trừ tồn kho ngay khi tạo đơn (kèm lockForUpdate chống bán vượt tồn) và ghi nhận inventory_movements.
 * - Áp giá khuyến mãi theo sản phẩm trước, áp mã giảm giá lên tổng tạm tính sau đó.
 * - Tạo bản ghi thanh toán COD hoặc Chuyển khoản với trạng thái ban đầu 'pending' (Chưa thanh toán).
 */
class CheckoutService
{
    public function __construct(
        private readonly CartService $cartService,
        private readonly ProductPresentationService $productPresentation,
        private readonly CouponManagementService $couponService
    ) {
    }

    /**
     * Lấy danh sách địa chỉ giao hàng đã lưu của người dùng.
     *
     * @return Collection<int, object>
     */
    public function getUserAddresses(int $userId): Collection
    {
        return DB::table('addresses')
            ->where('MaNguoiDung', $userId)
            ->orderByDesc('MacDinh')
            ->orderByDesc('MaDiaChi')
            ->get();
    }

    /**
     * Thực hiện đặt hàng trong giao dịch CSDL an toàn.
     *
     * @param array<string, mixed> $data
     * @return array{status: string, message: string, order?: object}
     */
    public function placeOrder(
        int $userId,
        string $sessionKey,
        array $data,
        ?string $couponCode = null
    ): array {
        try {
            $order = DB::transaction(function () use ($userId, $sessionKey, $data, $couponCode): object {
                $cart = $this->cartService->resolveCart($userId, $sessionKey, false);
                if ($cart === null) {
                    throw new RuntimeException('Giỏ hàng của bạn đang trống.');
                }

                $cartItems = DB::table('cart_items')
                    ->where('MaGioHang', $cart->MaGioHang)
                    ->orderBy('MaChiTietGioHang')
                    ->get();

                if ($cartItems->isEmpty()) {
                    throw new RuntimeException('Giỏ hàng của bạn đang trống.');
                }

                $productIds = $cartItems->pluck('MaSanPham')->map(static fn ($id): int => (int) $id)->all();

                // Khóa các dòng sản phẩm trong transaction để chống bán vượt tồn
                $lockedProducts = DB::table('products')
                    ->join('categories', 'categories.MaDanhMuc', '=', 'products.MaDanhMuc')
                    ->select([
                        'products.MaSanPham',
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
                    ->whereIn('products.MaSanPham', $productIds)
                    ->lockForUpdate()
                    ->get();

                $enrichedProducts = $this->productPresentation
                    ->enrichProducts($lockedProducts)
                    ->keyBy('MaSanPham');

                $now = Carbon::now();
                $subtotal = 0.0;
                $orderItemsPayload = [];

                foreach ($cartItems as $cartItem) {
                    $product = $enrichedProducts->get($cartItem->MaSanPham);
                    $qty = (int) $cartItem->SoLuong;

                    if (
                        $product === null ||
                        $product->NgayXoa !== null ||
                        $product->TrangThai !== 'active' ||
                        $product->NgayXoaDanhMuc !== null ||
                        $product->TrangThaiDanhMuc !== 'active'
                    ) {
                        throw new RuntimeException('Một số sản phẩm trong giỏ đã ngừng kinh doanh. Vui lòng kiểm tra lại giỏ hàng.');
                    }

                    if ((int) $product->SoLuongTon < $qty) {
                        throw new RuntimeException(sprintf(
                            'Sản phẩm "%s" chỉ còn %d sản phẩm trong kho (bạn đang chọn %d).',
                            $product->TenSanPham,
                            (int) $product->SoLuongTon,
                            $qty
                        ));
                    }

                    // Giá khuyến mãi theo sản phẩm áp trước
                    $unitPrice = round((float) ($product->GiaSauGiam ?? $product->Gia), 2);
                    $lineTotal = round($unitPrice * $qty, 2);
                    $subtotal += $lineTotal;

                    $orderItemsPayload[] = [
                        'MaSanPham' => (int) $product->MaSanPham,
                        'TenSanPham' => (string) $product->TenSanPham,
                        'MaSKU' => (string) $product->MaSKU,
                        'DonGia' => $unitPrice,
                        'SoLuong' => $qty,
                        'ThanhTien' => $lineTotal,
                        'NgayTao' => $now,
                    ];
                }

                $subtotal = round($subtotal, 2);
                $discountAmount = 0.0;
                $couponId = null;

                // Mã giảm giá áp lên tổng tạm tính sau đó (tối đa 1 mã/đơn)
                if ($couponCode !== null && trim($couponCode) !== '') {
                    $normalizedCode = mb_strtoupper(trim($couponCode));
                    DB::table('coupons')->where('MaUuDai', $normalizedCode)->lockForUpdate()->first();

                    $couponCheck = $this->couponService->validateAndCalculateDiscount(
                        $normalizedCode,
                        $subtotal,
                        $userId
                    );

                    if (!$couponCheck['valid']) {
                        throw new RuntimeException($couponCheck['message']);
                    }

                    $discountAmount = (float) $couponCheck['discount_amount'];
                    $couponId = (int) $couponCheck['coupon']->MaPhieuGiamGia;
                }

                $shippingFee = 0.0;
                $total = max(0.0, round($subtotal - $discountAmount + $shippingFee, 2));

                $addressId = $this->resolveOrderAddress($userId, $data, $now);
                $fullAddress = sprintf(
                    '%s, %s, %s, %s',
                    trim((string) $data['DiaChiChiTiet']),
                    trim((string) $data['PhuongXa']),
                    trim((string) $data['QuanHuyen']),
                    trim((string) $data['TinhThanh'])
                );

                $orderNumber = $this->generateUniqueOrderNumber($now);

                $orderId = (int) DB::table('orders')->insertGetId([
                    'MaNguoiDung' => $userId,
                    'MaDiaChi' => $addressId,
                    'MaPhieuGiamGia' => $couponId,
                    'SoDonHang' => $orderNumber,
                    'TrangThai' => OrderStatus::PREPARING,
                    'TenNguoiNhan' => trim((string) $data['TenNguoiNhan']),
                    'SoDienThoaiNguoiNhan' => trim((string) $data['SoDienThoaiNguoiNhan']),
                    'DiaChiGiaoHang' => $fullAddress,
                    'TamTinh' => $subtotal,
                    'TienGiam' => $discountAmount,
                    'PhiVanChuyen' => $shippingFee,
                    'TongTien' => $total,
                    'GhiChu' => !empty($data['GhiChu']) ? trim((string) $data['GhiChu']) : null,
                    'DatLuc' => $now,
                    'NgayTao' => $now,
                    'NgayCapNhat' => $now,
                ]);

                foreach ($orderItemsPayload as $itemRow) {
                    $itemRow['MaDonHang'] = $orderId;
                    DB::table('order_items')->insert($itemRow);

                    // Trừ tồn kho ngay khi tạo đơn và ghi nhận biến động kho
                    DB::table('products')
                        ->where('MaSanPham', $itemRow['MaSanPham'])
                        ->decrement('SoLuongTon', $itemRow['SoLuong'], ['NgayCapNhat' => $now]);

                    DB::table('inventory_movements')->insert([
                        'MaSanPham' => $itemRow['MaSanPham'],
                        'MaNguoiThucHien' => $userId,
                        'ThayDoiSoLuong' => -$itemRow['SoLuong'],
                        'LyDo' => 'Trừ tồn kho khi tạo đơn hàng ' . $orderNumber,
                        'LoaiThamChieu' => 'order',
                        'MaThamChieu' => $orderId,
                        'NgayTao' => $now,
                    ]);
                }

                if ($couponId !== null) {
                    DB::table('coupons')
                        ->where('MaPhieuGiamGia', $couponId)
                        ->increment('SoLanDaSuDung', 1, ['NgayCapNhat' => $now]);

                    DB::table('coupon_redemptions')->insert([
                        'MaPhieuGiamGia' => $couponId,
                        'MaNguoiDung' => $userId,
                        'MaDonHang' => $orderId,
                        'TienGiam' => $discountAmount,
                        'SuDungLuc' => $now,
                    ]);
                }

                DB::table('payments')->insert([
                    'MaDonHang' => $orderId,
                    'PhuongThuc' => $data['PhuongThuc'],
                    'TrangThai' => 'pending',
                    'MaGiaoDichNhaCungCap' => null,
                    'SoTien' => $total,
                    'ThanhToanLuc' => null,
                    'NgayTao' => $now,
                    'NgayCapNhat' => $now,
                ]);

                DB::table('cart_items')->where('MaGioHang', $cart->MaGioHang)->delete();

                return DB::table('orders')->where('MaDonHang', $orderId)->first();
            });

            return [
                'status' => 'ok',
                'message' => 'Đặt hàng thành công! Mã đơn hàng của bạn là ' . $order->SoDonHang . '.',
                'order' => $order,
            ];
        } catch (RuntimeException $exception) {
            return [
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Lấy thông tin đơn hàng vừa đặt thuộc quyền sở hữu của khách hàng.
     *
     * @return array{order: object, items: Collection<int, object>, payment: ?object, coupon: ?object}|null
     */
    public function getOrderForCustomer(string $orderNumber, int $userId): ?array
    {
        $order = DB::table('orders')
            ->where('SoDonHang', $orderNumber)
            ->where('MaNguoiDung', $userId)
            ->first();

        if ($order === null) {
            return null;
        }

        $items = DB::table('order_items')
            ->leftJoin('products', 'products.MaSanPham', '=', 'order_items.MaSanPham')
            ->select([
                'order_items.*',
                'products.DuongDan as DuongDanSanPham',
            ])
            ->where('order_items.MaDonHang', $order->MaDonHang)
            ->orderBy('order_items.MaChiTietDonHang')
            ->get();

        $payment = DB::table('payments')
            ->where('MaDonHang', $order->MaDonHang)
            ->orderByDesc('MaThanhToan')
            ->first();

        $coupon = $order->MaPhieuGiamGia !== null
            ? DB::table('coupons')->where('MaPhieuGiamGia', $order->MaPhieuGiamGia)->first()
            : null;

        return [
            'order' => $order,
            'items' => $items,
            'payment' => $payment,
            'coupon' => $coupon,
        ];
    }

    /**
     * Xác định hoặc lưu địa chỉ giao hàng vào sổ địa chỉ `addresses`.
     *
     * @param array<string, mixed> $data
     */
    private function resolveOrderAddress(int $userId, array $data, Carbon $now): ?int
    {
        if (!empty($data['MaDiaChi'])) {
            $existing = DB::table('addresses')
                ->where('MaDiaChi', (int) $data['MaDiaChi'])
                ->where('MaNguoiDung', $userId)
                ->first();

            if ($existing !== null) {
                return (int) $existing->MaDiaChi;
            }
        }

        $hasAnyAddress = DB::table('addresses')->where('MaNguoiDung', $userId)->exists();
        $shouldSave = !empty($data['luu_dia_chi']) || !$hasAnyAddress;

        if (!$shouldSave) {
            return null;
        }

        return (int) DB::table('addresses')->insertGetId([
            'MaNguoiDung' => $userId,
            'TenNguoiNhan' => trim((string) $data['TenNguoiNhan']),
            'SoDienThoai' => trim((string) $data['SoDienThoaiNguoiNhan']),
            'TinhThanh' => trim((string) $data['TinhThanh']),
            'QuanHuyen' => trim((string) $data['QuanHuyen']),
            'PhuongXa' => trim((string) $data['PhuongXa']),
            'DiaChiChiTiet' => trim((string) $data['DiaChiChiTiet']),
            'MacDinh' => !$hasAnyAddress,
            'NgayTao' => $now,
            'NgayCapNhat' => $now,
        ]);
    }

    /**
     * Sinh mã đơn hàng duy nhất theo định dạng DH-YYYYMMDD-XXXXXX.
     */
    private function generateUniqueOrderNumber(Carbon $now): string
    {
        do {
            $candidate = sprintf('DH-%s-%s', $now->format('Ymd'), Str::upper(Str::random(6)));
            $exists = DB::table('orders')->where('SoDonHang', $candidate)->exists();
        } while ($exists);

        return $candidate;
    }
}
