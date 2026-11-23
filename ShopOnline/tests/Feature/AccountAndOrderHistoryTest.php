<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\AccountService;
use App\Support\OrderStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountAndOrderHistoryTest extends TestCase
{
    use DatabaseTransactions;

    public function test_customer_can_render_a_pending_bank_transfer_order_detail(): void
    {
        $response = $this->view('account.orders.show', [
            'order' => (object) [
                'SoDonHang' => 'CM-TEST-ORDER',
                'NgayTao' => '2026-11-16 10:00:00',
                'TrangThai' => OrderStatus::PREPARING,
                'TamTinh' => 100000,
                'SoTienGiamGia' => 0,
                'PhiVanChuyen' => 0,
                'TongThanhToan' => 100000,
                'TenNguoiNhan' => 'Khách hàng thử nghiệm',
                'SoDienThoaiNhan' => '0900000000',
                'DiaChiGiaoHang' => 'Cao Lãnh, Đồng Tháp',
                'GhiChu' => null,
            ],
            'items' => collect([
                (object) [
                    'TenSanPham' => 'Sản phẩm thử nghiệm',
                    'SKU' => 'TEST-SKU',
                    'DonGia' => 100000,
                    'SoLuong' => 1,
                    'ThanhTien' => 100000,
                ],
            ]),
            'payment' => (object) [
                'PhuongThuc' => OrderStatus::PAYMENT_METHOD_BANK_TRANSFER,
                'TrangThai' => OrderStatus::PAYMENT_STATUS_PENDING,
            ],
            'coupon' => null,
            'bankTransferConfig' => [],
        ]);

        $response->assertSee('Thông tin chuyển khoản ngân hàng');
        $response->assertSee('Chưa thanh toán');
        $response->assertSee('class="badge bg-warning text-dark"', false);
    }

    public function test_customer_account_page_renders_recent_order_payment_status(): void
    {
        $response = $this->view('account.index', [
            'user' => (object) [
                'HoTen' => 'Khách hàng thử nghiệm',
                'Email' => 'customer@example.com',
                'SoDienThoai' => '0900000000',
            ],
            'addresses' => collect(),
            'errors' => new \Illuminate\Support\ViewErrorBag(),
            'recentOrders' => collect([
                (object) [
                    'SoDonHang' => 'CM-TEST-ORDER',
                    'NgayTao' => '2026-11-16 10:00:00',
                    'PhuongThucThanhToan' => OrderStatus::PAYMENT_METHOD_COD,
                    'TrangThaiThanhToan' => OrderStatus::PAYMENT_STATUS_PENDING,
                    'TongThanhToan' => 100000,
                    'TrangThai' => OrderStatus::PREPARING,
                ],
            ]),
        ]);

        $response->assertSee('Thông tin cá nhân');
        $response->assertSee('Chưa thanh toán');
        $response->assertSee('class="badge bg-warning text-dark ms-1"', false);
    }

    public function test_customer_order_list_uses_the_total_field_expected_by_the_view(): void
    {
        $customer = $this->createCustomer();
        $orderNumber = 'CM-TEST-' . strtoupper(uniqid());
        $orderId = (int) DB::table('orders')->insertGetId([
            'MaNguoiDung' => $customer->getKey(),
            'SoDonHang' => $orderNumber,
            'TrangThai' => 'pending',
            'TenNguoiNhan' => $customer->HoTen,
            'SoDienThoaiNguoiNhan' => '0900000000',
            'DiaChiGiaoHang' => 'Cao Lãnh, Đồng Tháp',
            'TamTinh' => 100000,
            'TienGiam' => 0,
            'PhiVanChuyen' => 0,
            'TongTien' => 100000,
        ]);
        DB::table('payments')->insert([
            'MaDonHang' => $orderId,
            'PhuongThuc' => OrderStatus::PAYMENT_METHOD_COD,
            'TrangThai' => OrderStatus::PAYMENT_STATUS_PENDING,
            'SoTien' => 100000,
        ]);

        $order = app(AccountService::class)
            ->paginateCustomerOrders((int) $customer->getKey())
            ->getCollection()
            ->first();

        $this->assertNotNull($order);
        $this->assertSame(100000.0, (float) $order->TongThanhToan);

        $this->actingAs($customer)
            ->get(route('account.index'))
            ->assertOk()
            ->assertSee($orderNumber)
            ->assertSee('100.000 đ');

        $this->actingAs($customer)
            ->get(route('account.orders.index'))
            ->assertOk()
            ->assertSee('0900000000');
    }

    public function test_customer_can_view_order_detail_using_database_field_names(): void
    {
        $customer = $this->createCustomer();
        $orderNumber = 'CM-DETAIL-' . strtoupper(uniqid());
        $orderId = (int) DB::table('orders')->insertGetId([
            'MaNguoiDung' => $customer->getKey(),
            'SoDonHang' => $orderNumber,
            'TrangThai' => OrderStatus::PREPARING,
            'TenNguoiNhan' => $customer->HoTen,
            'SoDienThoaiNguoiNhan' => '0912345678',
            'DiaChiGiaoHang' => 'Cao Lãnh, Đồng Tháp',
            'TamTinh' => 100000,
            'TienGiam' => 10000,
            'PhiVanChuyen' => 0,
            'TongTien' => 90000,
        ]);
        DB::table('order_items')->insert([
            'MaDonHang' => $orderId,
            'MaSanPham' => null,
            'TenSanPham' => 'Sản phẩm đã đặt',
            'MaSKU' => 'SKU-DETAIL',
            'DonGia' => 100000,
            'SoLuong' => 1,
            'ThanhTien' => 100000,
        ]);
        DB::table('payments')->insert([
            'MaDonHang' => $orderId,
            'PhuongThuc' => OrderStatus::PAYMENT_METHOD_COD,
            'TrangThai' => OrderStatus::PAYMENT_STATUS_PENDING,
            'SoTien' => 90000,
        ]);

        $this->actingAs($customer)
            ->get(route('account.orders.show', $orderNumber))
            ->assertOk()
            ->assertSee('0912345678')
            ->assertSee('SKU-DETAIL')
            ->assertSee('10.000 đ')
            ->assertSee('90.000 đ');
    }

    private function createCustomer(): User
    {
        return User::query()->create([
            'HoTen' => 'Khách Hàng A7',
            'Email' => 'khachhang_a7_' . uniqid() . '@example.com',
            'SoDienThoai' => '0901112233',
            'MatKhau' => Hash::make('Password123'),
            'TrangThai' => 'active',
        ]);
    }

    private function createOrderForCustomer(User $customer, string $status = OrderStatus::PREPARING): array
    {
        $categoryId = (int) DB::table('categories')->insertGetId([
            'TenDanhMuc' => 'Danh mục A7',
            'DuongDan' => 'danh-muc-a7-' . uniqid(),
            'DangHoatDong' => 1,
        ]);

        $productId = (int) DB::table('products')->insertGetId([
            'MaDanhMuc' => $categoryId,
            'TenSanPham' => 'Mô hình Gundam A7',
            'DuongDan' => 'mo-hinh-gundam-a7-' . uniqid(),
            'SKU' => 'SKU-A7-' . strtoupper(uniqid()),
            'GiaBan' => 450000,
            'SoLuongTon' => 8,
            'TrangThai' => 'active',
        ]);

        $orderNumber = 'CM20261113' . random_int(1000, 9999);
        $orderId = (int) DB::table('orders')->insertGetId([
            'SoDonHang' => $orderNumber,
            'MaNguoiDung' => $customer->getKey(),
            'TrangThai' => $status,
            'TamTinh' => 900000,
            'SoTienGiamGia' => 0,
            'PhiVanChuyen' => 0,
            'TongThanhToan' => 900000,
            'MaPhieuGiamGia' => null,
            'TenNguoiNhan' => $customer->HoTen,
            'SoDienThoaiNhan' => '0901112233',
            'DiaChiGiaoHang' => '123 Nguyễn Huệ, TP. Cao Lãnh, Đồng Tháp',
        ]);

        DB::table('order_items')->insert([
            'MaDonHang' => $orderId,
            'MaSanPham' => $productId,
            'TenSanPham' => 'Mô hình Gundam A7',
            'SKU' => 'SKU-A7',
            'DonGia' => 450000,
            'SoLuong' => 2,
            'ThanhTien' => 900000,
        ]);

        DB::table('payments')->insert([
            'MaDonHang' => $orderId,
            'PhuongThuc' => OrderStatus::PAYMENT_METHOD_COD,
            'TrangThai' => OrderStatus::PAYMENT_STATUS_PENDING,
            'SoTien' => 900000,
        ]);

        return [$orderId, $orderNumber, $productId];
    }

    public function test_customer_account_page_renders_with_order_history_link(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer)
            ->get(route('account.index'))
            ->assertOk()
            ->assertSee(route('account.orders.index'), false);
    }

    public function test_customer_can_update_profile_and_change_password(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer)
            ->put(route('account.profile.update'), [
                'HoTen' => 'Nguyễn Văn Đổi Tên',
                'SoDienThoai' => '0988776655',
            ])
            ->assertRedirect(route('account.index'));

        $this->assertDatabaseHas('users', [
            'MaNguoiDung' => $customer->getKey(),
            'HoTen' => 'Nguyễn Văn Đổi Tên',
            'SoDienThoai' => '0988776655',
        ]);

        // Sai mật khẩu hiện tại
        $this->actingAs($customer)
            ->put(route('account.password.update'), [
                'MatKhauHienTai' => 'WrongPass123',
                'MatKhau' => 'NewPassword123',
                'MatKhau_confirmation' => 'NewPassword123',
            ])
            ->assertSessionHasErrors('MatKhauHienTai');

        // Đúng mật khẩu hiện tại
        $this->actingAs($customer)
            ->put(route('account.password.update'), [
                'MatKhauHienTai' => 'Password123',
                'MatKhau' => 'NewPassword123',
                'MatKhau_confirmation' => 'NewPassword123',
            ])
            ->assertRedirect(route('account.index'));

        $customer->refresh();
        $this->assertTrue(Hash::check('NewPassword123', $customer->MatKhau));
    }

    public function test_customer_can_cancel_order_when_preparing_or_prepared_and_restores_stock(): void
    {
        $customer = $this->createCustomer();
        [$orderId, $orderNumber, $productId] = $this->createOrderForCustomer($customer, OrderStatus::PREPARED);

        $this->actingAs($customer)
            ->post(route('account.orders.cancel', $orderNumber))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('orders', [
            'MaDonHang' => $orderId,
            'TrangThai' => OrderStatus::CANCELLED,
        ]);

        // Tồn kho được hoàn lại: 8 + 2 = 10
        $this->assertDatabaseHas('products', [
            'MaSanPham' => $productId,
            'SoLuongTon' => 10,
        ]);
    }

    public function test_customer_cannot_cancel_order_when_shipping_or_later(): void
    {
        $customer = $this->createCustomer();
        [$orderId, $orderNumber, $productId] = $this->createOrderForCustomer($customer, OrderStatus::SHIPPING);

        $this->actingAs($customer)
            ->post(route('account.orders.cancel', $orderNumber))
            ->assertRedirect()
            ->assertSessionHas('error');

        $this->assertDatabaseHas('orders', [
            'MaDonHang' => $orderId,
            'TrangThai' => OrderStatus::SHIPPING,
        ]);

        // Tồn kho giữ nguyên 8
        $this->assertDatabaseHas('products', [
            'MaSanPham' => $productId,
            'SoLuongTon' => 8,
        ]);
    }
}

