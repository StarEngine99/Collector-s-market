<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\OrderStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountAndOrderHistoryTest extends TestCase
{
    use DatabaseTransactions;

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
