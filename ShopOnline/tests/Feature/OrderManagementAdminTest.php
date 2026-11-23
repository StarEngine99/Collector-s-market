<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\OrderStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Feature test cho chức năng B3: Quản lý đơn hàng phía quản trị
 * (lọc trạng thái, cập nhật 5 trạng thái giao hàng + Hoàn tất + Đã hủy, hoàn kho khi hủy, xác nhận thanh toán).
 */
class OrderManagementAdminTest extends TestCase
{
    private User $adminUser;
    private int $productId;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('orders.view', static fn (User $user): bool => $user->hasPermission('orders.view'));
        Gate::define('orders.update-status', static fn (User $user): bool => $user->hasPermission('orders.update-status'));

        $adminId = (int) DB::table('users')->insertGetId([
            'HoTen' => 'Admin Đơn Hàng',
            'Email' => 'admin_b3_' . uniqid() . '@example.com',
            'MatKhau' => Hash::make('AdminPass123'),
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);

        $adminRole = DB::table('roles')->where('TenVaiTro', 'administrator')->first();
        if ($adminRole !== null) {
            DB::table('user_roles')->insertOrIgnore([
                'MaNguoiDung' => $adminId,
                'MaVaiTro' => $adminRole->MaVaiTro,
            ]);
        }

        $this->adminUser = User::query()->findOrFail($adminId);

        $cat = DB::table('categories')->first();
        $catId = $cat !== null
            ? (int) $cat->MaDanhMuc
            : (int) DB::table('categories')->insertGetId([
                'TenDanhMuc' => 'Thẻ bài B3',
                'DuongDan' => 'the-bai-b3-' . uniqid(),
                'TrangThai' => 'active',
                'NgayTao' => Carbon::now(),
                'NgayCapNhat' => Carbon::now(),
            ]);

        $this->productId = (int) DB::table('products')->insertGetId([
            'MaDanhMuc' => $catId,
            'MaSKU' => 'SKU-B3-' . strtoupper(substr(uniqid(), -5)),
            'TenSanPham' => 'Hộp Thẻ Bài Pokemon Booster Box',
            'DuongDan' => 'hop-the-bai-pokemon-' . uniqid(),
            'Gia' => 500000,
            'SoLuongTon' => 8,
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);
    }

    public function test_admin_cap_nhat_trang_thai_don_cod_sang_hoan_tat_tu_dong_danh_dau_da_thanh_toan(): void
    {
        $now = Carbon::now();
        $orderId = (int) DB::table('orders')->insertGetId([
            'SoDonHang' => 'DH-B3-COD-' . strtoupper(substr(uniqid(), -4)),
            'TrangThai' => OrderStatus::PREPARING,
            'TenNguoiNhan' => 'Khách COD',
            'SoDienThoaiNguoiNhan' => '0909111222',
            'DiaChiGiaoHang' => 'Cao Lãnh, Đồng Tháp',
            'TamTinh' => 500000,
            'TienGiam' => 0,
            'PhiVanChuyen' => 0,
            'TongTien' => 500000,
            'DatLuc' => $now,
            'NgayTao' => $now,
            'NgayCapNhat' => $now,
        ]);

        DB::table('payments')->insert([
            'MaDonHang' => $orderId,
            'PhuongThuc' => 'cod',
            'TrangThai' => 'pending',
            'SoTien' => 500000,
            'NgayTao' => $now,
            'NgayCapNhat' => $now,
        ]);

        // Đi qua các trạng thái giao hàng đến Hoàn tất
        foreach ([
            OrderStatus::PREPARED,
            OrderStatus::SHIPPING,
            OrderStatus::IN_TRANSIT,
            OrderStatus::DELIVERING,
            OrderStatus::COMPLETED,
        ] as $nextStatus) {
            $this->actingAs($this->adminUser)
                ->patch(route('admin.orders.status', $orderId), ['TrangThai' => $nextStatus])
                ->assertSessionHas('success');
        }

        $orderAfter = DB::table('orders')->where('MaDonHang', $orderId)->first();
        $this->assertSame(OrderStatus::COMPLETED, $orderAfter->TrangThai);

        $paymentAfter = DB::table('payments')->where('MaDonHang', $orderId)->first();
        $this->assertSame('paid', $paymentAfter->TrangThai);
        $this->assertNotNull($paymentAfter->ThanhToanLuc);
    }

    public function test_admin_huy_don_hang_se_hoan_lai_ton_kho(): void
    {
        $now = Carbon::now();
        $orderId = (int) DB::table('orders')->insertGetId([
            'SoDonHang' => 'DH-B3-CANCEL-' . strtoupper(substr(uniqid(), -4)),
            'TrangThai' => OrderStatus::PREPARING,
            'TenNguoiNhan' => 'Khách Hủy',
            'SoDienThoaiNguoiNhan' => '0909333444',
            'DiaChiGiaoHang' => 'Sa Đéc, Đồng Tháp',
            'TamTinh' => 1000000,
            'TienGiam' => 0,
            'PhiVanChuyen' => 0,
            'TongTien' => 1000000,
            'DatLuc' => $now,
            'NgayTao' => $now,
            'NgayCapNhat' => $now,
        ]);

        DB::table('order_items')->insert([
            'MaDonHang' => $orderId,
            'MaSanPham' => $this->productId,
            'TenSanPham' => 'Hộp Thẻ Bài Pokemon Booster Box',
            'MaSKU' => 'SKU-B3',
            'DonGia' => 500000,
            'SoLuong' => 2,
            'ThanhTien' => 1000000,
            'NgayTao' => $now,
        ]);

        $this->actingAs($this->adminUser)
            ->patch(route('admin.orders.status', $orderId), [
                'TrangThai' => OrderStatus::CANCELLED,
                'ly_do_huy' => 'Khách yêu cầu hủy',
            ])
            ->assertSessionHas('success');

        // Tồn kho ban đầu 8 + hoàn lại 2 = 10
        $productAfter = DB::table('products')->where('MaSanPham', $this->productId)->first();
        $this->assertSame(10, (int) $productAfter->SoLuongTon);
    }
}

