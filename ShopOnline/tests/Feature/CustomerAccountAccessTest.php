<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAccountAccessTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('users.manage', static fn (User $user): bool => ! $user->hasRole('customer'));
        Gate::define('customers.view', static fn (User $user): bool => ! $user->hasRole('customer'));
        Gate::define('customers.lock', static fn (User $user): bool => ! $user->hasRole('customer'));
        Gate::define('products.view', static fn (User $user): bool => false);
        Gate::define('coupons.manage', static fn (User $user): bool => false);
    }

    public function test_customer_can_manage_only_their_own_addresses_and_default_falls_back_after_delete(): void
    {
        $customer = $this->createCustomer();
        $otherCustomer = $this->createCustomer();
        $firstAddress = $this->addressData('Địa chỉ thứ nhất');
        $secondAddress = $this->addressData('Địa chỉ thứ hai');

        $this->actingAs($customer)
            ->post(route('account.addresses.store'), $firstAddress)
            ->assertRedirect(route('account.index'));

        $first = DB::table('addresses')->where('MaNguoiDung', $customer->getKey())->first();
        $this->assertNotNull($first);
        $this->assertSame(1, (int) $first->MacDinh);

        $this->actingAs($customer)
            ->post(route('account.addresses.store'), $secondAddress + ['MacDinh' => 1])
            ->assertRedirect(route('account.index'));

        $second = DB::table('addresses')
            ->where('MaNguoiDung', $customer->getKey())
            ->where('MaDiaChi', '!=', $first->MaDiaChi)
            ->first();
        $this->assertNotNull($second);
        $this->assertSame(1, (int) $second->MacDinh);
        $this->assertSame(0, (int) DB::table('addresses')->where('MaDiaChi', $first->MaDiaChi)->value('MacDinh'));

        $this->actingAs($customer)
            ->get(route('account.index'))
            ->assertOk()
            ->assertSee('Sổ địa chỉ giao hàng')
            ->assertSee($secondAddress['DiaChiChiTiet']);

        $this->actingAs($customer)
            ->put(route('account.addresses.update', $first->MaDiaChi), $this->addressData('Địa chỉ đã sửa'))
            ->assertRedirect(route('account.index'));
        $this->assertDatabaseHas('addresses', [
            'MaDiaChi' => $first->MaDiaChi,
            'DiaChiChiTiet' => 'Địa chỉ đã sửa',
        ]);

        $this->actingAs($otherCustomer)
            ->put(route('account.addresses.update', $second->MaDiaChi), $this->addressData('Không được sửa'))
            ->assertNotFound();

        $this->actingAs($otherCustomer)
            ->delete(route('account.addresses.destroy', $second->MaDiaChi))
            ->assertNotFound();

        $this->actingAs($customer)
            ->delete(route('account.addresses.destroy', $second->MaDiaChi))
            ->assertRedirect(route('account.index'));

        $this->assertSame(1, (int) DB::table('addresses')->where('MaDiaChi', $first->MaDiaChi)->value('MacDinh'));
        $this->assertDatabaseMissing('addresses', ['MaDiaChi' => $second->MaDiaChi]);
    }

    public function test_admin_can_create_update_and_soft_delete_customer_accounts_but_cannot_manage_admins(): void
    {
        $admin = $this->createUser('admin-accounts');
        $otherAdmin = $this->createUser('other-admin');
        $payload = [
            'HoTen' => 'Khách được tạo',
            'Email' => 'customer_' . uniqid() . '@example.com',
            'SoDienThoai' => '0901234567',
            'MatKhau' => 'CustomerPass123',
            'MatKhau_confirmation' => 'CustomerPass123',
        ];

        $this->actingAs($admin)
            ->get(route('admin.customers.create'))
            ->assertOk();

        $this->actingAs($admin)
            ->post(route('admin.customers.store'), $payload)
            ->assertRedirect();

        $customer = DB::table('users')->where('Email', $payload['Email'])->first();
        $this->assertNotNull($customer);
        $this->assertSame('active', $customer->TrangThai);
        $customerRoleId = DB::table('roles')->where('TenVaiTro', 'customer')->value('MaVaiTro');
        $this->assertDatabaseHas('user_roles', [
            'MaNguoiDung' => $customer->MaNguoiDung,
            'MaVaiTro' => $customerRoleId,
        ]);
        $this->assertFalse((new User())->newQuery()->whereKey($customer->MaNguoiDung)->first()->hasRole('administrator'));

        $this->actingAs($admin)
            ->get(route('admin.customers.edit', $customer->MaNguoiDung))
            ->assertOk();

        $this->actingAs($admin)
            ->put(route('admin.customers.update', $customer->MaNguoiDung), [
                'HoTen' => 'Tên đã cập nhật',
                'Email' => $payload['Email'],
                'SoDienThoai' => '0987654321',
                'MatKhau' => 'NewCustomerPass123',
                'MatKhau_confirmation' => 'NewCustomerPass123',
            ])
            ->assertRedirect();

        $updatedCustomer = User::query()->findOrFail($customer->MaNguoiDung);
        $this->assertSame('Tên đã cập nhật', $updatedCustomer->HoTen);
        $this->assertTrue(Hash::check('NewCustomerPass123', $updatedCustomer->MatKhau));

        $this->actingAs($admin)
            ->get(route('admin.customers.edit', $otherAdmin->getKey()))
            ->assertNotFound();

        $this->actingAs($admin)
            ->delete(route('admin.customers.destroy', $customer->MaNguoiDung))
            ->assertRedirect(route('admin.customers.index'));

        $this->assertDatabaseHas('users', [
            'MaNguoiDung' => $customer->MaNguoiDung,
            'TrangThai' => 'locked',
        ]);
        $this->assertNotNull(DB::table('users')->where('MaNguoiDung', $customer->MaNguoiDung)->value('NgayXoa'));
        $this->assertDatabaseHas('admin_audit_logs', [
            'MaNguoiThucHien' => $admin->getKey(),
            'HanhDong' => 'customer.delete',
            'MaDoiTuong' => $customer->MaNguoiDung,
        ]);
    }

    public function test_customer_cannot_access_admin_account_catalog_or_discount_management(): void
    {
        $customer = $this->createCustomer();

        $this->actingAs($customer)
            ->get(route('admin.customers.create'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('admin.products.index'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('admin.coupons.index'))
            ->assertForbidden();

        $this->actingAs($customer)
            ->get(route('admin.promotions.index'))
            ->assertForbidden();
    }

    private function createCustomer(): User
    {
        $customer = $this->createUser('customer');
        $roleId = DB::table('roles')->where('TenVaiTro', 'customer')->value('MaVaiTro');
        $this->assertNotNull($roleId);
        DB::table('user_roles')->insert([
            'MaNguoiDung' => $customer->getKey(),
            'MaVaiTro' => $roleId,
        ]);

        return $customer;
    }

    private function createUser(string $prefix): User
    {
        return User::query()->create([
            'HoTen' => 'Tài khoản kiểm thử',
            'Email' => $prefix . '_' . uniqid() . '@example.com',
            'SoDienThoai' => '0901112233',
            'MatKhau' => Hash::make('Password123'),
            'TrangThai' => 'active',
        ]);
    }

    /**
     * @return array<string, string>
     */
    private function addressData(string $address): array
    {
        return [
            'TenNguoiNhan' => 'Người nhận thử nghiệm',
            'SoDienThoai' => '0901112233',
            'TinhThanh' => 'Đồng Tháp',
            'QuanHuyen' => 'Cao Lãnh',
            'PhuongXa' => 'Phường 1',
            'DiaChiChiTiet' => $address,
        ];
    }
}
