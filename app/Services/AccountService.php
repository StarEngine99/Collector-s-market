<?php

namespace App\Services;

use App\Support\OrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Service xử lý Tài khoản cá nhân & Lịch sử đơn hàng của khách hàng (A7).
 */
class AccountService
{
    /**
     * Cập nhật thông tin hồ sơ cá nhân (Họ tên, Số điện thoại).
     *
     * @param array<string, mixed> $data
     */
    public function updateProfile(int $userId, array $data): bool
    {
        $affected = DB::table('users')
            ->where('MaNguoiDung', $userId)
            ->update([
                'HoTen' => trim((string) $data['HoTen']),
                'SoDienThoai' => !empty($data['SoDienThoai']) ? trim((string) $data['SoDienThoai']) : null,
                'NgayCapNhat' => Carbon::now(),
            ]);

        return $affected >= 0;
    }

    /**
     * Đổi mật khẩu tài khoản sau khi kiểm tra mật khẩu hiện tại.
     *
     * @return array{status: string, message: string}
     */
    public function changePassword(int $userId, string $currentPassword, string $newPassword): array
    {
        $user = DB::table('users')
            ->where('MaNguoiDung', $userId)
            ->whereNull('NgayXoa')
            ->first();

        if ($user === null) {
            return [
                'status' => 'not_found',
                'message' => 'Không tìm thấy tài khoản.',
            ];
        }

        if (!Hash::check($currentPassword, (string) $user->MatKhau)) {
            return [
                'status' => 'invalid_current',
                'message' => 'Mật khẩu hiện tại không chính xác.',
            ];
        }

        DB::table('users')
            ->where('MaNguoiDung', $userId)
            ->update([
                'MatKhau' => Hash::make($newPassword),
                'NgayCapNhat' => Carbon::now(),
            ]);

        return [
            'status' => 'ok',
            'message' => 'Đổi mật khẩu thành công.',
        ];
    }

    /**
     * Lấy danh sách đơn hàng của khách hàng có lọc theo trạng thái và phân trang.
     */
    public function paginateCustomerOrders(
        int $userId,
        ?string $status = null,
        int $perPage = 10
    ): LengthAwarePaginator {
        $latestPaymentSub = DB::table('payments')
            ->selectRaw('MaDonHang, MAX(MaThanhToan) as max_payment_id')
            ->groupBy('MaDonHang');

        $itemCountSub = DB::table('order_items')
            ->selectRaw('MaDonHang, SUM(SoLuong) as tong_so_luong')
            ->groupBy('MaDonHang');

        $query = DB::table('orders')
            ->leftJoinSub($latestPaymentSub, 'lp', function ($join): void {
                $join->on('lp.MaDonHang', '=', 'orders.MaDonHang');
            })
            ->leftJoin('payments', 'payments.MaThanhToan', '=', 'lp.max_payment_id')
            ->leftJoinSub($itemCountSub, 'ic', function ($join): void {
                $join->on('ic.MaDonHang', '=', 'orders.MaDonHang');
            })
            ->select([
                'orders.MaDonHang',
                'orders.SoDonHang',
                'orders.TrangThai',
                'orders.TenNguoiNhan',
                'orders.TamTinh',
                'orders.TienGiam',
                'orders.TongTien',
                'orders.DatLuc',
                'orders.NgayTao',
                'payments.PhuongThuc as PhuongThucThanhToan',
                'payments.TrangThai as TrangThaiThanhToan',
                DB::raw('COALESCE(ic.tong_so_luong, 0) as tong_so_luong'),
            ])
            ->where('orders.MaNguoiDung', $userId);

        if ($status !== null && array_key_exists($status, OrderStatus::labels())) {
            $query->where('orders.TrangThai', $status);
        }

        return $query
            ->orderByDesc('orders.NgayTao')
            ->orderByDesc('orders.MaDonHang')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Lấy chi tiết đơn hàng theo SoDonHang và kiểm tra quyền sở hữu của khách hàng.
     *
     * @return array{
     *     order: object,
     *     items: Collection<int, object>,
     *     payment: ?object,
     *     coupon: ?object
     * }|null
     */
    public function getCustomerOrderDetail(string $orderNumber, int $userId): ?array
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
}

