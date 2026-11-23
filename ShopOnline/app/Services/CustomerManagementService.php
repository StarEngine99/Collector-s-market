<?php

namespace App\Services;

use App\Support\OrderStatus;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

/**
 * Service quản lý khách hàng phía quản trị (B4):
 * - Danh sách khách hàng, tìm kiếm & lọc theo trạng thái tài khoản
 * - Xem chi tiết khách hàng, địa chỉ & lịch sử mua hàng
 * - Tạo/sửa tài khoản khách; ẩn mềm để giữ lịch sử đơn hàng
 * - Khóa / mở khóa tài khoản khách hàng (kèm ghi log admin_audit_logs)
 */
class CustomerManagementService
{
    /**
     * Phân trang danh sách khách hàng kèm tổng số đơn và tổng chi tiêu.
     * Chỉ bao gồm người dùng có vai trò customer và không có vai trò nội bộ khác.
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
                COALESCE(SUM(CASE WHEN TrangThai <> ? THEN TongTien ELSE 0 END), 0) AS TongChiTieu
            ', [OrderStatus::CANCELLED])
            ->groupBy('MaNguoiDung');

        $query = $this->manageableCustomerQuery()
            ->leftJoinSub($orderStatsSubquery, 'os', function ($join) {
                $join->on('os.MaNguoiDung', '=', 'u.MaNguoiDung');
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
        $customer = $this->manageableCustomerQuery()
            ->where('u.MaNguoiDung', $customerId)
            ->first([
                'u.MaNguoiDung',
                'u.HoTen',
                'u.Email',
                'u.SoDienThoai',
                'u.TrangThai',
                'u.NgayTao',
                'u.NgayCapNhat',
            ]);

        if (! $customer) {
            return null;
        }

        $addresses = DB::table('addresses')
            ->where('MaNguoiDung', $customerId)
            ->orderByDesc('MacDinh')
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
                'o.TienGiam as SoTienGiamGia',
                'o.PhiVanChuyen',
                'o.TongTien as TongThanhToan',
                'o.TenNguoiNhan',
                'o.SoDienThoaiNguoiNhan as SoDienThoaiNhan',
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

    public function findCustomer(int $customerId): ?object
    {
        return $this->manageableCustomerQuery()
            ->where('u.MaNguoiDung', $customerId)
            ->first([
                'u.MaNguoiDung',
                'u.HoTen',
                'u.Email',
                'u.SoDienThoai',
                'u.TrangThai',
            ]);
    }

    /**
     * Create a customer account and assign only the customer role.
     *
     * @param array<string, mixed> $data
     */
    public function createCustomer(array $data, int $adminUserId, ?string $ipAddress = null): int
    {
        return DB::transaction(function () use ($data, $adminUserId, $ipAddress): int {
            $customerRoleId = DB::table('roles')->where('TenVaiTro', 'customer')->value('MaVaiTro');
            if ($customerRoleId === null) {
                throw new RuntimeException('Không tìm thấy vai trò customer để gán cho tài khoản mới.');
            }

            $now = now();
            $customerId = (int) DB::table('users')->insertGetId([
                'HoTen' => trim((string) $data['HoTen']),
                'Email' => mb_strtolower(trim((string) $data['Email'])),
                'SoDienThoai' => ! empty($data['SoDienThoai']) ? trim((string) $data['SoDienThoai']) : null,
                'MatKhau' => Hash::make((string) $data['MatKhau']),
                'TrangThai' => 'active',
                'NgayTao' => $now,
                'NgayCapNhat' => $now,
            ]);

            DB::table('user_roles')->insert([
                'MaNguoiDung' => $customerId,
                'MaVaiTro' => (int) $customerRoleId,
            ]);

            $this->logCustomerChange(
                $adminUserId,
                'customer.create',
                $customerId,
                'Tạo tài khoản khách hàng #' . $customerId . ' (' . $data['Email'] . ')',
                $ipAddress
            );

            return $customerId;
        });
    }

    /**
     * Update a customer profile and optionally reset its password.
     *
     * @param array<string, mixed> $data
     */
    public function updateCustomer(int $customerId, array $data, int $adminUserId, ?string $ipAddress = null): bool
    {
        return DB::transaction(function () use ($customerId, $data, $adminUserId, $ipAddress): bool {
            $customer = $this->manageableCustomerQuery()
                ->where('u.MaNguoiDung', $customerId)
                ->lockForUpdate()
                ->first(['u.MaNguoiDung', 'u.Email']);

            if ($customer === null || $customerId === $adminUserId) {
                return false;
            }

            $values = [
                'HoTen' => trim((string) $data['HoTen']),
                'Email' => mb_strtolower(trim((string) $data['Email'])),
                'SoDienThoai' => ! empty($data['SoDienThoai']) ? trim((string) $data['SoDienThoai']) : null,
                'NgayCapNhat' => now(),
            ];
            if (! empty($data['MatKhau'])) {
                $values['MatKhau'] = Hash::make((string) $data['MatKhau']);
                $values['MaGhiNho'] = null;
            }

            DB::table('users')
                ->where('MaNguoiDung', $customerId)
                ->update($values);

            $this->logCustomerChange(
                $adminUserId,
                'customer.update',
                $customerId,
                'Cập nhật tài khoản khách hàng #' . $customerId . ' (' . $customer->Email . ')',
                $ipAddress
            );

            return true;
        });
    }

    /**
     * Soft-delete a customer so order history and related records remain intact.
     */
    public function deleteCustomer(int $customerId, int $adminUserId, ?string $ipAddress = null): bool
    {
        return DB::transaction(function () use ($customerId, $adminUserId, $ipAddress): bool {
            $customer = $this->manageableCustomerQuery()
                ->where('u.MaNguoiDung', $customerId)
                ->lockForUpdate()
                ->first(['u.MaNguoiDung', 'u.Email']);

            if ($customer === null || $customerId === $adminUserId) {
                return false;
            }

            DB::table('users')
                ->where('MaNguoiDung', $customerId)
                ->update([
                    'TrangThai' => 'locked',
                    'MaGhiNho' => null,
                    'NgayXoa' => now(),
                    'NgayCapNhat' => now(),
                ]);

            $this->logCustomerChange(
                $adminUserId,
                'customer.delete',
                $customerId,
                'Ẩn tài khoản khách hàng #' . $customerId . ' (' . $customer->Email . ')',
                $ipAddress
            );

            return true;
        });
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
            $customer = $this->manageableCustomerQuery()
                ->where('u.MaNguoiDung', $customerId)
                ->lockForUpdate()
                ->first(['u.MaNguoiDung', 'u.Email', 'u.TrangThai', 'u.MaGhiNho']);

            if (! $customer) {
                return [
                    'status' => 'not_found',
                    'message' => 'Không tìm thấy tài khoản khách hàng.',
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
                    'MaNguoiThucHien' => $adminUserId,
                    'HanhDong' => $newStatus === 'locked' ? 'customer.lock' : 'customer.unlock',
                    'LoaiDoiTuong' => 'users',
                    'MaDoiTuong' => $customerId,
                    'TomTat' => sprintf(
                        '%s tài khoản khách hàng #%d (%s -> %s)',
                        $newStatus === 'locked' ? 'Khóa' : 'Mở khóa',
                        $customerId,
                        $oldStatus,
                        $newStatus
                    ),
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

    private function manageableCustomerQuery(): Builder
    {
        return DB::table('users as u')
            ->whereNull('u.NgayXoa')
            ->whereExists(function ($sub): void {
                $sub->select(DB::raw(1))
                    ->from('user_roles as ur')
                    ->join('roles as r', 'r.MaVaiTro', '=', 'ur.MaVaiTro')
                    ->whereColumn('ur.MaNguoiDung', 'u.MaNguoiDung')
                    ->where('r.TenVaiTro', 'customer');
            })
            ->whereNotExists(function ($sub): void {
                $sub->select(DB::raw(1))
                    ->from('user_roles as ur')
                    ->join('roles as r', 'r.MaVaiTro', '=', 'ur.MaVaiTro')
                    ->whereColumn('ur.MaNguoiDung', 'u.MaNguoiDung')
                    ->where('r.TenVaiTro', '<>', 'customer');
            });
    }

    private function logCustomerChange(
        int $adminUserId,
        string $action,
        int $customerId,
        string $summary,
        ?string $ipAddress
    ): void {
        DB::table('admin_audit_logs')->insert([
            'MaNguoiThucHien' => $adminUserId,
            'HanhDong' => $action,
            'LoaiDoiTuong' => 'users',
            'MaDoiTuong' => $customerId,
            'TomTat' => $summary,
            'DiaChiIP' => $ipAddress,
        ]);
    }
}
