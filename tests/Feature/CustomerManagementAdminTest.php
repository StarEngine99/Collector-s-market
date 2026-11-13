<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerManagementAdminTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('customers.view', fn (User $user) => true);
        Gate::define('customers.lock', fn (User $user) => true);
    }

    private function createAdmin(): User
    {
        return User::query()->create([
            'HoTen' => 'Admin B4',
            'Email' => 'admin_b4_' . uniqid() . '@example.com',
            'MatKhau' => Hash::make('AdminPass123'),
            'TrangThai' => 'active',
        ]);
    }

    private function createCustomer(): User
    {
        return User::query()->create([
            'HoTen' => 'Khách Hàng B4',
            'Email' => 'khach_b4_' . uniqid() . '@example.com',
            'SoDienThoai' => '0905556677',
            'MatKhau' => Hash::make('CustomerPass123'),
            'TrangThai' => 'active',
        ]);
    }

    public function test_admin_can_view_customers_and_lock_unlock_customer_account(): void
    {
        $admin = $this->createAdmin();
        $customer = $this->createCustomer();

        // Xem danh sách & chi tiết lịch sử mua hàng
        $this->actingAs($admin)
            ->get(route('admin.customers.index'))
            ->assertOk()
            ->assertSee($customer->Email);

        $this->actingAs($admin)
            ->get(route('admin.customers.show', $customer->getKey()))
            ->assertOk()
            ->assertSee($customer->HoTen);

        // Khóa tài khoản khách hàng
        $this->actingAs($admin)
            ->patch(route('admin.customers.status', $customer->getKey()), [
                'TrangThai' => 'locked',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'MaNguoiDung' => $customer->getKey(),
            'TrangThai' => 'locked',
        ]);

        $this->assertDatabaseHas('admin_audit_logs', [
            'MaNguoiDung' => $admin->getKey(),
            'HanhDong' => 'customer.lock',
            'LoaiDoiTuong' => 'users',
            'MaDoiTuong' => $customer->getKey(),
        ]);

        // Mở khóa lại tài khoản khách hàng
        $this->actingAs($admin)
            ->patch(route('admin.customers.status', $customer->getKey()), [
                'TrangThai' => 'active',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'MaNguoiDung' => $customer->getKey(),
            'TrangThai' => 'active',
        ]);
    }
}
