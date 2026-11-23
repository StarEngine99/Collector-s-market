<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminNavigationTest extends TestCase
{
    use DatabaseTransactions;

    public function test_admin_login_opens_dashboard_and_account_page_offers_admin_navigation(): void
    {
        $email = 'admin_navigation_' . uniqid() . '@example.com';
        $userId = (int) DB::table('users')->insertGetId([
            'HoTen' => 'Admin Navigation Test',
            'Email' => $email,
            'MatKhau' => Hash::make('Password123'),
            'TrangThai' => 'active',
        ]);
        $adminRoleId = DB::table('roles')->where('TenVaiTro', 'administrator')->value('MaVaiTro');
        $this->assertNotNull($adminRoleId);

        DB::table('user_roles')->insert([
            'MaNguoiDung' => $userId,
            'MaVaiTro' => $adminRoleId,
        ]);

        $this->post(route('login.submit'), [
            'Email' => $email,
            'MatKhau' => 'Password123',
        ])->assertRedirect(route('admin.dashboard'));

        $this->get(route('admin.dashboard'))->assertOk();

        $this->get(route('account.index'))
            ->assertOk()
            ->assertSee('Quản trị')
            ->assertSee('Bảng điều khiển quản trị')
            ->assertSee(route('admin.dashboard'), false);
    }
}
