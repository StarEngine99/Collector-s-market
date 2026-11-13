<?php

namespace App\Services;

use App\Support\OrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Service quản lý Đơn hàng phía quản trị (B3) và dùng chung xử lý Hủy đơn + Hoàn tồn kho (A7).
 */
class OrderManagementService
{
    /**
     * Danh sách đơn hàng có tìm kiếm, lọc theo trạng thái đơn, trạng thái thanh toán và phân trang.
     */
    public function paginateOrders(
        ?string $keyword = null,
        ?string $status = null,
        ?string $paymentStatus = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $latestPaymentSub = DB::table('payments')
            ->selectRaw('MaDonHang, MAX(MaThanhToan) as max_payment_id')
            ->groupBy('MaDonHang');

        $query = DB::table('orders')
            ->leftJoin('users', 'users.MaNguoiDung', '=', 'orders.MaNguoiDung')
            ->leftJoinSub($latestPaymentSub, 'lp', function ($join): void {
                $join->on('lp.MaDonHang', '=', 'orders.MaDonHang');
            })
            ->leftJoin('payments', 'payments.MaThanhToan', '=', 'lp.max_payment_id')
            ->select([
                'orders.MaDonHang',
                'orders.MaNguoiDung',
                'orders.SoDonHang',
                'orders.TrangThai',
                'orders.TenNguoiNhan',
                'orders.SoDienThoaiNguoiNhan',
                'orders.TamTinh',
                'orders.TienGiam',
                'orders.TongTien',
                'orders.DatLuc',
                'orders.NgayTao',
                'orders.NgayCapNhat',
                'users.Email as EmailKhachHang',
                'payments.PhuongThuc as PhuongThucThanhToan',
                'payments.TrangThai as TrangThaiThanhToan',
            ]);

        $trimmed = trim((string) $keyword);
        if ($trimmed !== '') {
            $escaped = str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $trimmed);
            $query->where(function ($sub) use ($escaped): void {
                $sub->where('orders.SoDonHang', 'like', '%' . $escaped . '%')
                    ->orWhere('orders.TenNguoiNhan', 'like', '%' . $escaped . '%')
                    ->orWhere('orders.SoDienThoaiNguoiNhan', 'like', '%' . $escaped . '%')
                    ->orWhere('users.Email', 'like', '%' . $escaped . '%');
            });
        }

        if ($status !== null && array_key_exists($status, OrderStatus::labels())) {
            $query->where('orders.TrangThai', $status);
        }

        if ($paymentStatus !== null && in_array($paymentStatus, ['pending', 'paid'], true)) {
            $query->where('payments.TrangThai', $paymentStatus);
        }

        return $query
            ->orderByDesc('orders.NgayTao')
            ->orderByDesc('orders.MaDonHang')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Lấy chi tiết đầy đủ của một đơn hàng theo ID.
     *
     * @return array{
     *     order: object,
     *     items: Collection<int, object>,
     *     payment: ?object,
     *     coupon: ?object,
     *     customer: ?object
     * }|null
     */
    public function getOrderDetailById(int $orderId): ?array
    {
        $order = DB::table('orders')->where('MaDonHang', $orderId)->first();
        if ($order === null) {
            return null;
        }

        $items = DB::table('order_items')
            ->leftJoin('products', 'products.MaSanPham', '=', 'order_items.MaSanPham')
            ->select([
                'order_items.*',
                'products.DuongDan as DuongDanSanPham',
            ])
            ->where('order_items.MaDonHang', $orderId)
            ->orderBy('order_items.MaChiTietDonHang')
            ->get();

        $payment = DB::table('payments')
            ->where('MaDonHang', $orderId)
            ->orderByDesc('MaThanhToan')
            ->first();

        $coupon = $order->MaPhieuGiamGia !== null
            ? DB::table('coupons')->where('MaPhieuGiamGia', $order->MaPhieuGiamGia)->first()
            : null;

        $customer = $order->MaNguoiDung !== null
            ? DB::table('users')
                ->select(['MaNguoiDung', 'HoTen', 'Email', 'SoDienThoai', 'TrangThai'])
                ->where('MaNguoiDung', $order->MaNguoiDung)
                ->first()
            : null;

        return [
            'order' => $order,
            'items' => $items,
            'payment' => $payment,
            'coupon' => $coupon,
            'customer' => $customer,
        ];
    }

    /**
     * Cập nhật trạng thái đơn hàng phía quản trị (B3):
     * - Nếu chuyển sang 'cancelled': hoàn lại tồn kho và hoàn lượt dùng mã giảm giá.
     * - Nếu chuyển sang 'completed' và phương thức là COD: tự động đánh dấu thanh toán 'paid'.
     *
     * @return array{status: string, message: string}
     */
    public function updateOrderStatus(
        int $orderId,
        string $newStatus,
        ?int $actorId = null,
        ?string $ipAddress = null,
        ?string $cancelReason = null
    ): array {
        if (!array_key_exists($newStatus, OrderStatus::labels())) {
            return [
                'status' => 'error',
                'message' => 'Trạng thái đơn hàng không hợp lệ.',
            ];
        }

        try {
            return DB::transaction(function () use ($orderId, $newStatus, $actorId, $ipAddress, $cancelReason): array {
                $order = DB::table('orders')
                    ->where('MaDonHang', $orderId)
                    ->lockForUpdate()
                    ->first();

                if ($order === null) {
                    return [
                        'status' => 'not_found',
                        'message' => 'Không tìm thấy đơn hàng.',
                    ];
                }

                $oldStatus = (string) $order->TrangThai;

                if ($oldStatus === $newStatus) {
                    return [
                        'status' => 'ok',
                        'message' => 'Đơn hàng đã ở trạng thái "' . OrderStatus::label($newStatus) . '".',
                    ];
                }

                if ($oldStatus === OrderStatus::CANCELLED) {
                    throw new RuntimeException('Đơn hàng đã bị hủy trước đó, không thể chuyển sang trạng thái khác.');
                }

                if ($oldStatus === OrderStatus::COMPLETED) {
                    throw new RuntimeException('Đơn hàng đã hoàn tất, không thể thay đổi trạng thái.');
                }

                $now = Carbon::now();

                // Nếu chuyển sang trạng thái Đã hủy -> hoàn lại tồn kho và mã giảm giá
                if ($newStatus === OrderStatus::CANCELLED) {
                    $this->restoreInventoryAndCouponOnCancel(
                        $order,
                        $actorId,
                        $cancelReason ?: 'Quản trị viên hủy đơn hàng ' . $order->SoDonHang,
                        $now
                    );
                }

                DB::table('orders')
                    ->where('MaDonHang', $orderId)
                    ->update([
                        'TrangThai' => $newStatus,
                        'NgayCapNhat' => $now,
                    ]);

                // Nếu đơn hoàn tất và thanh toán COD -> tự động đánh dấu Đã thanh toán
                if ($newStatus === OrderStatus::COMPLETED) {
                    $payment = DB::table('payments')
                        ->where('MaDonHang', $orderId)
                        ->orderByDesc('MaThanhToan')
                        ->first();

                    if ($payment !== null && $payment->PhuongThuc === 'cod' && $payment->TrangThai !== 'paid') {
                        DB::table('payments')
                            ->where('MaThanhToan', $payment->MaThanhToan)
                            ->update([
                                'TrangThai' => 'paid',
                                'ThanhToanLuc' => $now,
                                'NgayCapNhat' => $now,
                            ]);
                    }
                }

                $this->writeAuditLog(
                    $actorId,
                    'orders.update-status',
                    'order',
                    $orderId,
                    sprintf(
                        'Đổi trạng thái đơn %s từ "%s" sang "%s"',
                        $order->SoDonHang,
                        OrderStatus::label($oldStatus),
                        OrderStatus::label($newStatus)
                    ),
                    $ipAddress
                );

                return [
                    'status' => 'ok',
                    'message' => sprintf(
                        'Đã cập nhật trạng thái đơn %s sang "%s".',
                        $order->SoDonHang,
                        OrderStatus::label($newStatus)
                    ),
                ];
            });
        } catch (RuntimeException $exception) {
            return [
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Xác nhận trạng thái thanh toán của đơn hàng (Chưa thanh toán / Đã thanh toán).
     *
     * @return array{status: string, message: string}
     */
    public function updatePaymentStatus(
        int $orderId,
        string $newPaymentStatus,
        ?string $providerReference = null,
        ?int $actorId = null,
        ?string $ipAddress = null
    ): array {
        if (!in_array($newPaymentStatus, ['pending', 'paid'], true)) {
            return [
                'status' => 'error',
                'message' => 'Trạng thái thanh toán không hợp lệ.',
            ];
        }

        return DB::transaction(function () use ($orderId, $newPaymentStatus, $providerReference, $actorId, $ipAddress): array {
            $order = DB::table('orders')->where('MaDonHang', $orderId)->first();
            if ($order === null) {
                return [
                    'status' => 'not_found',
                    'message' => 'Không tìm thấy đơn hàng.',
                ];
            }

            $payment = DB::table('payments')
                ->where('MaDonHang', $orderId)
                ->orderByDesc('MaThanhToan')
                ->lockForUpdate()
                ->first();

            $now = Carbon::now();
            $cleanRef = $providerReference !== null && trim($providerReference) !== ''
                ? trim($providerReference)
                : null;

            if ($payment === null) {
                DB::table('payments')->insert([
                    'MaDonHang' => $orderId,
                    'PhuongThuc' => 'bank_transfer',
                    'TrangThai' => $newPaymentStatus,
                    'MaGiaoDichNhaCungCap' => $cleanRef,
                    'SoTien' => (float) $order->TongTien,
                    'ThanhToanLuc' => $newPaymentStatus === 'paid' ? $now : null,
                    'NgayTao' => $now,
                    'NgayCapNhat' => $now,
                ]);
            } else {
                $updateData = [
                    'TrangThai' => $newPaymentStatus,
                    'ThanhToanLuc' => $newPaymentStatus === 'paid' ? ($payment->ThanhToanLuc ?? $now) : null,
                    'NgayCapNhat' => $now,
                ];

                if ($cleanRef !== null) {
                    $updateData['MaGiaoDichNhaCungCap'] = $cleanRef;
                }

                DB::table('payments')
                    ->where('MaThanhToan', $payment->MaThanhToan)
                    ->update($updateData);
            }

            $this->writeAuditLog(
                $actorId,
                'orders.update-payment',
                'order',
                $orderId,
                sprintf(
                    'Cập nhật thanh toán đơn %s sang "%s"',
                    $order->SoDonHang,
                    OrderStatus::paymentStatusLabel($newPaymentStatus)
                ),
                $ipAddress
            );

            return [
                'status' => 'ok',
                'message' => sprintf(
                    'Đã cập nhật trạng thái thanh toán đơn %s sang "%s".',
                    $order->SoDonHang,
                    OrderStatus::paymentStatusLabel($newPaymentStatus)
                ),
            ];
        });
    }

    /**
     * Khách hàng hủy đơn của chính mình (A7):
     * Chỉ được hủy khi đơn đang ở trạng thái "Đang chuẩn bị hàng" hoặc "Đã chuẩn bị hàng".
     *
     * @return array{status: string, message: string}
     */
    public function cancelOrderByCustomer(string $orderNumber, int $userId): array
    {
        try {
            return DB::transaction(function () use ($orderNumber, $userId): array {
                $order = DB::table('orders')
                    ->where('SoDonHang', $orderNumber)
                    ->where('MaNguoiDung', $userId)
                    ->lockForUpdate()
                    ->first();

                if ($order === null) {
                    return [
                        'status' => 'not_found',
                        'message' => 'Không tìm thấy đơn hàng.',
                    ];
                }

                if (!OrderStatus::canCustomerCancel((string) $order->TrangThai)) {
                    throw new RuntimeException(
                        'Bạn chỉ có thể hủy đơn khi đơn ở trạng thái "Đang chuẩn bị hàng" hoặc "Đã chuẩn bị hàng".'
                    );
                }

                $now = Carbon::now();

                $this->restoreInventoryAndCouponOnCancel(
                    $order,
                    $userId,
                    'Hoàn tồn kho do khách hàng hủy đơn ' . $order->SoDonHang,
                    $now
                );

                DB::table('orders')
                    ->where('MaDonHang', $order->MaDonHang)
                    ->update([
                        'TrangThai' => OrderStatus::CANCELLED,
                        'NgayCapNhat' => $now,
                    ]);

                return [
                    'status' => 'ok',
                    'message' => 'Đã hủy đơn hàng ' . $order->SoDonHang . ' thành công.',
                ];
            });
        } catch (RuntimeException $exception) {
            return [
                'status' => 'error',
                'message' => $exception->getMessage(),
            ];
        }
    }

    /**
     * Hoàn lại tồn kho sản phẩm và hoàn lại lượt dùng mã giảm giá khi đơn hàng bị hủy.
     */
    private function restoreInventoryAndCouponOnCancel(
        object $order,
        ?int $actorId,
        string $reason,
        Carbon $now
    ): void {
        $items = DB::table('order_items')
            ->where('MaDonHang', $order->MaDonHang)
            ->get();

        foreach ($items as $item) {
            if ($item->MaSanPham === null) {
                continue;
            }

            $qty = (int) $item->SoLuong;
            if ($qty <= 0) {
                continue;
            }

            DB::table('products')
                ->where('MaSanPham', $item->MaSanPham)
                ->increment('SoLuongTon', $qty, ['NgayCapNhat' => $now]);

            DB::table('inventory_movements')->insert([
                'MaSanPham' => $item->MaSanPham,
                'MaNguoiThucHien' => $actorId,
                'ThayDoiSoLuong' => $qty,
                'LyDo' => $reason,
                'LoaiThamChieu' => 'order_cancel',
                'MaThamChieu' => $order->MaDonHang,
                'NgayTao' => $now,
            ]);
        }

        if ($order->MaPhieuGiamGia !== null) {
            $coupon = DB::table('coupons')
                ->where('MaPhieuGiamGia', $order->MaPhieuGiamGia)
                ->lockForUpdate()
                ->first();

            if ($coupon !== null && (int) $coupon->SoLanDaSuDung > 0) {
                DB::table('coupons')
                    ->where('MaPhieuGiamGia', $coupon->MaPhieuGiamGia)
                    ->decrement('SoLanDaSuDung', 1, ['NgayCapNhat' => $now]);
            }

            DB::table('coupon_redemptions')
                ->where('MaDonHang', $order->MaDonHang)
                ->delete();
        }
    }

    /**
     * Ghi nhận nhật ký thao tác quản trị vào bảng `admin_audit_logs`.
     */
    private function writeAuditLog(
        ?int $actorId,
        string $action,
        string $subjectType,
        int $subjectId,
        string $summary,
        ?string $ipAddress
    ): void {
        DB::table('admin_audit_logs')->insert([
            'MaNguoiThucHien' => $actorId,
            'HanhDong' => $action,
            'LoaiDoiTuong' => $subjectType,
            'MaDoiTuong' => $subjectId,
            'TomTat' => mb_substr($summary, 0, 500),
            'DiaChiIP' => $ipAddress,
            'NgayTao' => Carbon::now(),
        ]);
    }
}
