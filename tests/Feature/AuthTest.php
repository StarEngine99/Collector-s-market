<?php

namespace Tests\Feature;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

/**
 * Feature test cho chức năng A6: Đăng ký, Đăng nhập, Đăng xuất, Khóa tài khoản và Quên/Đặt lại mật khẩu.
 */
class AuthTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Mail::fake();
    }

    public function test_khach_hang_co_the_dang_ky_tai_khoan_moi_va_mat_khau_duoc_ma_hoa(): void
    {
        $email = 'khachmoi_' . uniqid() . '@example.com';

        $response = $this->post(route('register.submit'), [
            'HoTen' => 'Nguyễn Văn Khách',
            'Email' => $email,
            'SoDienThoai' => '0912345678',
            'MatKhau' => 'MatKhau@123',
            'MatKhau_confirmation' => 'MatKhau@123',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $user = DB::table('users')->where('Email', $email)->first();
        $this->assertNotNull($user);
        $this->assertSame('active', $user->TrangThai);
        $this->assertNotSame('MatKhau@123', $user->MatKhau);
        $this->assertTrue(Hash::check('MatKhau@123', (string) $user->MatKhau));

        // Kiểm tra đã gán role customer trong user_roles (nếu bảng roles đã seed customer)
        $customerRole = DB::table('roles')->where('TenVaiTro', 'customer')->first();
        if ($customerRole !== null) {
            $hasRole = DB::table('user_roles')
                ->where('MaNguoiDung', $user->MaNguoiDung)
                ->where('MaVaiTro', $customerRole->MaVaiTro)
                ->exists();
            $this->assertTrue($hasRole);
        }
    }

    public function test_dang_ky_that_bai_khi_email_bi_trung(): void
    {
        $email = 'trungemail_' . uniqid() . '@example.com';

        DB::table('users')->insert([
            'HoTen' => 'Người Dùng Cũ',
            'Email' => $email,
            'MatKhau' => Hash::make('MatKhau@123'),
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);

        $response = $this->from(route('register'))->post(route('register.submit'), [
            'HoTen' => 'Người Dùng Mới',
            'Email' => $email,
            'MatKhau' => 'MatKhau@123',
            'MatKhau_confirmation' => 'MatKhau@123',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['Email']);
        $this->assertGuest();
    }

    public function test_dang_nhap_thanh_cong_va_giu_phien_ghi_nho(): void
    {
        $email = 'dangnhap_' . uniqid() . '@example.com';

        $userId = DB::table('users')->insertGetId([
            'HoTen' => 'Trần Thị B',
            'Email' => $email,
            'MatKhau' => Hash::make('CorrectPass123'),
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);

        $response = $this->post(route('login.submit'), [
            'Email' => $email,
            'MatKhau' => 'CorrectPass123',
            'ghi_nho' => '1',
        ]);

        $response->assertRedirect(route('home'));
        $this->assertAuthenticated();

        $userAfterLogin = DB::table('users')->where('MaNguoiDung', $userId)->first();
        $this->assertNotEmpty($userAfterLogin->MaGhiNho);
    }

    public function test_dang_nhap_that_bai_khi_sai_mat_khau(): void
    {
        $email = 'saimatkhau_' . uniqid() . '@example.com';

        DB::table('users')->insert([
            'HoTen' => 'Lê Văn C',
            'Email' => $email,
            'MatKhau' => Hash::make('MatKhauDung123'),
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);

        $response = $this->from(route('login'))->post(route('login.submit'), [
            'Email' => $email,
            'MatKhau' => 'MatKhauSai999',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['Email']);
        $this->assertGuest();
    }

    public function test_tai_khoan_bi_khoa_khong_the_dang_nhap(): void
    {
        $email = 'bikhoa_' . uniqid() . '@example.com';

        DB::table('users')->insert([
            'HoTen' => 'Tài Khoản Bị Khóa',
            'Email' => $email,
            'MatKhau' => Hash::make('MatKhauDung123'),
            'TrangThai' => 'locked',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);

        $response = $this->from(route('login'))->post(route('login.submit'), [
            'Email' => $email,
            'MatKhau' => 'MatKhauDung123',
        ]);

        $response->assertRedirect(route('login'));
        $response->assertSessionHasErrors(['Email']);
        $this->assertGuest();
    }

    public function test_quen_mat_khau_tra_thong_bao_chung_khi_tat_demo_va_hien_link_khi_bat_demo(): void
    {
        $email = 'quenmk_' . uniqid() . '@example.com';

        DB::table('users')->insert([
            'HoTen' => 'Phạm Văn D',
            'Email' => $email,
            'MatKhau' => Hash::make('OldPassword123'),
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);

        // 1. Khi PASSWORD_RESET_DEMO = false (mặc định): trả thông báo chung cho cả email không tồn tại
        Config::set('storefront.password_reset_demo', false);

        $resNonExistent = $this->from(route('password.request'))->post(route('password.email'), [
            'Email' => 'khongtontai_' . uniqid() . '@example.com',
        ]);
        $resNonExistent->assertRedirect(route('password.request'));
        $resNonExistent->assertSessionHas('success', 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.');
        $resNonExistent->assertSessionMissing('demo_reset_url');

        // 2. Khi PASSWORD_RESET_DEMO = true: hiện demo_reset_url
        Config::set('storefront.password_reset_demo', true);

        $resDemo = $this->from(route('password.request'))->post(route('password.email'), [
            'Email' => $email,
        ]);
        $resDemo->assertRedirect(route('password.request'));
        $resDemo->assertSessionHas('demo_reset_url');
    }

    public function test_dat_lai_mat_khau_thanh_cong_va_tu_choi_link_het_han(): void
    {
        $email = 'resetmk_' . uniqid() . '@example.com';

        $userId = DB::table('users')->insertGetId([
            'HoTen' => 'Hoàng Thị E',
            'Email' => $email,
            'MatKhau' => Hash::make('OldPassword123'),
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);

        $rawToken = 'token_khoi_phuc_hop_le_1234567890';

        // Trường hợp 1: Token đã quá hạn (> 60 phút)
        DB::table('password_reset_tokens')->updateOrInsert(
            ['Email' => $email],
            [
                'Token' => Hash::make($rawToken),
                'NgayTao' => Carbon::now()->subMinutes(61),
            ]
        );

        $expiredResponse = $this->from(route('password.reset', ['token' => $rawToken]))
            ->post(route('password.update'), [
                'token' => $rawToken,
                'Email' => $email,
                'MatKhau' => 'NewPassword456',
                'MatKhau_confirmation' => 'NewPassword456',
            ]);

        $expiredResponse->assertSessionHas('error');
        $this->assertDatabaseMissing('password_reset_tokens', ['Email' => $email]);

        // Trường hợp 2: Token còn hạn -> đổi mật khẩu thành công
        DB::table('password_reset_tokens')->insert([
            'Email' => $email,
            'Token' => Hash::make($rawToken),
            'NgayTao' => Carbon::now(),
        ]);

        $validResponse = $this->post(route('password.update'), [
            'token' => $rawToken,
            'Email' => $email,
            'MatKhau' => 'NewPassword456',
            'MatKhau_confirmation' => 'NewPassword456',
        ]);

        $validResponse->assertRedirect(route('login'));
        $validResponse->assertSessionHas('success');

        $updatedUser = DB::table('users')->where('MaNguoiDung', $userId)->first();
        $this->assertTrue(Hash::check('NewPassword456', (string) $updatedUser->MatKhau));
        $this->assertDatabaseMissing('password_reset_tokens', ['Email' => $email]);
    }
}

