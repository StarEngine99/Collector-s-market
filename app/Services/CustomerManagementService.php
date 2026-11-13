<?php

namespace App\Services;

use App\Support\OrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

/**
 * Service quản lý khách hàng phía quản trị (B4):
 * - Danh sách khách hàng, tìm kiếm & lọc theo trạng thái tài khoản
 * - Xem chi tiết khách hàng, địa chỉ & lịch sử mua hàng
 * - Khóa / mở khóa tài khoản khách hàng (kèm ghi log admin_audit_logs)
 */
class CustomerManagementService
{
    /**
     * Phân trang danh sách khách hàng kèm tổng số đơn và tổng chi tiêu.
     * Lọc những tài khoản đóng vai trò customer hoặc chưa gán vai trò quản trị/nhân viên.
     */
    public function paginateCustomers(
        ?string $keyword = null,
        ?string $status = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $orderStatsSubquery = DB::table('orders')
            ->selectRaw('
                MaNguoiDung,
                COUNT(*) AS TongSoDon,
                COALESCE(SUM(CASE WHEN TrangThai <> ? THEN TongThanhToan ELSE 0 END), 0) AS TongChiTieu
            ', [OrderStatus::CANCELLED])
            ->groupBy('MaNguoiDung');

        $query = DB::table('users as u')
            ->leftJoinSub($orderStatsSubquery, 'os', function ($join) {
                $join->on('os.MaNguoiDung', '=', 'u.MaNguoiDung');
            })
            ->whereNotExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('user_roles as ur')
                    ->join('roles as r', 'r.MaVaiTro', '=', 'ur.MaVaiTro')
                    ->whereColumn('ur.MaNguoiDung', 'u.MaNguoiDung')
                    ->whereIn('r.TenVaiTro', ['administrator', 'staff', 'warehouse_staff', 'order_staff', 'marketing_staff']);
            })
            ->select([
                'u.MaNguoiDung',
                'u.HoTen',
                'u.Email',
                'u.SoDienThoai',
                'u.TrangThai',
                'u.NgayTao',
                DB::raw('COALESCE(os.TongSoDon, 0) AS TongSoDon'),
                DB::raw('COALESCE(os.TongChiTieu, 0) AS TongChiTieu'),
            ]);

        if ($keyword !== null && trim($keyword) !== '') {
            $kw = '%' . trim($keyword) . '%';
            $query->where(function ($sub) use ($kw) {
                $sub->where('u.HoTen', 'like', $kw)
                    ->orWhere('u.Email', 'like', $kw)
                    ->orWhere('u.SoDienThoai', 'like', $kw);
            });
        }

        if ($status !== null && in_array($status, ['active', 'locked'], true)) {
            $query->where('u.TrangThai', $status);
        }

        return $query
            ->orderByDesc('u.MaNguoiDung')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Lấy thông tin chi tiết khách hàng, danh sách địa chỉ và lịch sử đơn hàng.
     *
     * @return array{customer: object, addresses: \Illuminate\Support\Collection, orders: \Illuminate\Support\Collection, stats: array{total_orders: int, completed_orders: int, cancelled_orders: int, total_spent: float}}|null
     */
    public function getCustomerDetail(int $customerId): ?array
    {
        $customer = DB::table('users')
            ->where('MaNguoiDung', $customerId)
            ->first([
                'MaNguoiDung',
                'HoTen',
                'Email',
                'SoDienThoai',
                'TrangThai',
                'NgayTao',
                'NgayCapNhat',
            ]);

        if (! $customer) {
            return null;
        }

        $addresses = DB::table('addresses')
            ->where('MaNguoiDung', $customerId)
            ->orderByDesc('LaMacDinh')
            ->orderByDesc('MaDiaChi')
            ->get();

        $orders = DB::table('orders as o')
            ->leftJoin('payments as p', 'p.MaDonHang', '=', 'o.MaDonHang')
            ->where('o.MaNguoiDung', $customerId)
            ->orderByDesc('o.NgayTao')
            ->orderByDesc('o.MaDonHang')
            ->get([
                'o.MaDonHang',
                'o.SoDonHang',
                'o.TrangThai',
                'o.TamTinh',
                'o.SoTienGiamGia',
                'o.PhiVanChuyen',
                'o.TongThanhToan',
                'o.TenNguoiNhan',
                'o.SoDienThoaiNhan',
                'o.DiaChiGiaoHang',
                'o.NgayTao',
                'p.PhuongThuc as PhuongThucThanhToan',
                'p.TrangThai as TrangThaiThanhToan',
            ]);

        $totalOrders = $orders->count();
        $completedOrders = $orders->where('TrangThai', OrderStatus::COMPLETED)->count();
        $cancelledOrders = $orders->where('TrangThai', OrderStatus::CANCELLED)->count();
        $totalSpent = (float) $orders
            ->where('TrangThai', '!=', OrderStatus::CANCELLED)
            ->sum('TongThanhToan');

        return [
            'customer' => $customer,
            'addresses' => $addresses,
            'orders' => $orders,
            'stats' => [
                'total_orders' => $totalOrders,
                'completed_orders' => $completedOrders,
                'cancelled_orders' => $cancelledOrders,
                'total_spent' => $totalSpent,
            ],
        ];
    }

    /**
     * Khóa hoặc mở khóa tài khoản khách hàng.
     *
     * @return array{status: string, message: string, new_status?: string}
     */
    public function updateCustomerStatus(
        int $customerId,
        string $newStatus,
        ?int $adminUserId = null,
        ?string $ipAddress = null
    ): array {
        if (! in_array($newStatus, ['active', 'locked'], true)) {
            return [
                'status' => 'invalid_status',
                'message' => 'Trạng thái tài khoản không hợp lệ.',
            ];
        }

        return DB::transaction(function () use ($customerId, $newStatus, $adminUserId, $ipAddress): array {
            $customer = DB::table('users')
                ->where('MaNguoiDung', $customerId)
                ->lockForUpdate()
                ->first();

            if (! $customer) {
                return [
                    'status' => 'not_found',
                    'message' => 'Không tìm thấy tài khoản khách hàng.',
                ];
            }

            // Không cho phép khóa tài khoản quản trị viên
            $isAdmin = DB::table('user_roles as ur')
                ->join('roles as r', 'r.MaVaiTro', '=', 'ur.MaVaiTro')
                ->where('ur.MaNguoiDung', $customerId)
                ->where('r.TenVaiTro', 'administrator')
                ->exists();

            if ($isAdmin && $newStatus === 'locked') {
                return [
                    'status' => 'forbidden',
                    'message' => 'Không được phép khóa tài khoản quản trị viên.',
                ];
            }

            $oldStatus = (string) $customer->TrangThai;

            if ($oldStatus === $newStatus) {
                return [
                    'status' => 'unchanged',
                    'message' => 'Trạng thái tài khoản không thay đổi.',
                    'new_status' => $newStatus,
                ];
            }

            DB::table('users')
                ->where('MaNguoiDung', $customerId)
                ->update([
                    'TrangThai' => $newStatus,
                    'MaGhiNho' => $newStatus === 'locked' ? null : $customer->MaGhiNho,
                    'NgayCapNhat' => now(),
                ]);

            if ($adminUserId !== null) {
                DB::table('admin_audit_logs')->insert([
                    'MaNguoiDung' => $adminUserId,
                    'HanhDong' => $newStatus === 'locked' ? 'customer.lock' : 'customer.unlock',
                    'LoaiDoiTuong' => 'users',
                    'MaDoiTuong' => $customerId,
                    'GiaTriCu' => json_encode(['TrangThai' => $oldStatus], JSON_UNESCAPED_UNICODE),
                    'GiaTriMoi' => json_encode(['TrangThai' => $newStatus], JSON_UNESCAPED_UNICODE),
                    'DiaChiIP' => $ipAddress,
                ]);
            }

            $msg = $newStatus === 'locked'
                ? 'Đã khóa tài khoản khách hàng ' . $customer->Email . '.'
                : 'Đã mở khóa tài khoản khách hàng ' . $customer->Email . '.';

            return [
                'status' => 'ok',
                'message' => $msg,
                'new_status' => $newStatus,
            ];
        });
    }
}
