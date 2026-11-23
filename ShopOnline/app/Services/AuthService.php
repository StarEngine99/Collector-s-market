<?php

namespace App\Services;

use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;

/**
 * Service xử lý nghiệp vụ Đăng ký, Đăng nhập, Đăng xuất, Gộp giỏ hàng phiên và Quên mật khẩu (A6).
 */
class AuthService
{
    /**
     * Thời gian hiệu lực của mã đặt lại mật khẩu (phút).
     */
    private const RESET_TOKEN_TTL_MINUTES = 60;

    /**
     * Đăng ký tài khoản khách hàng mới, gán vai trò `customer` và gộp giỏ hàng tạm (nếu có).
     *
     * @param array<string, mixed> $data
     */
    public function register(array $data, ?string $guestSessionKey = null): int
    {
        return DB::transaction(function () use ($data, $guestSessionKey): int {
            $now = Carbon::now();
            $email = mb_strtolower(trim((string) $data['Email']));

            $userId = (int) DB::table('users')->insertGetId([
                'HoTen' => trim((string) $data['HoTen']),
                'Email' => $email,
                'SoDienThoai' => !empty($data['SoDienThoai']) ? trim((string) $data['SoDienThoai']) : null,
                'MatKhau' => Hash::make((string) $data['MatKhau']),
                'TrangThai' => 'active',
                'NgayTao' => $now,
                'NgayCapNhat' => $now,
            ]);

            $customerRole = DB::table('roles')
                ->where('TenVaiTro', 'customer')
                ->first();

            if ($customerRole !== null) {
                DB::table('user_roles')->insertOrIgnore([
                    'MaNguoiDung' => $userId,
                    'MaVaiTro' => $customerRole->MaVaiTro,
                ]);
            }

            if ($guestSessionKey !== null && $guestSessionKey !== '') {
                $this->mergeGuestCartIntoUserCart($userId, $guestSessionKey);
            }

            return $userId;
        });
    }

    /**
     * Xác thực thông tin đăng nhập, kiểm tra khóa tài khoản và gộp giỏ hàng tạm theo phiên.
     *
     * @return array{status: string, message?: string, user?: object}
     */
    public function attemptLogin(
        string $email,
        string $password,
        bool $remember = false,
        ?string $guestSessionKey = null
    ): array {
        $normalizedEmail = mb_strtolower(trim($email));

        $user = DB::table('users')
            ->where('Email', $normalizedEmail)
            ->whereNull('NgayXoa')
            ->first();

        if ($user === null || !Hash::check($password, (string) $user->MatKhau)) {
            return [
                'status' => 'invalid_credentials',
                'message' => 'Email hoặc mật khẩu không chính xác.',
            ];
        }

        if ($user->TrangThai === 'locked') {
            return [
                'status' => 'locked',
                'message' => 'Tài khoản của bạn đã bị khóa. Vui lòng liên hệ quản trị viên để được hỗ trợ.',
            ];
        }

        Auth::loginUsingId((int) $user->MaNguoiDung, $remember);

        if ($guestSessionKey !== null && $guestSessionKey !== '') {
            $this->mergeGuestCartIntoUserCart((int) $user->MaNguoiDung, $guestSessionKey);
        }

        return [
            'status' => 'ok',
            'user' => $user,
        ];
    }

    /**
     * Gộp giỏ hàng lưu tạm theo phiên (KhoaPhien) của khách chưa đăng nhập vào giỏ hàng của tài khoản.
     */
    public function mergeGuestCartIntoUserCart(int $userId, string $guestSessionKey): void
    {
        DB::transaction(function () use ($userId, $guestSessionKey): void {
            $guestCart = DB::table('carts')
                ->where('KhoaPhien', $guestSessionKey)
                ->whereNull('MaNguoiDung')
                ->first();

            if ($guestCart === null) {
                return;
            }

            $now = Carbon::now();
            $userCart = DB::table('carts')
                ->where('MaNguoiDung', $userId)
                ->first();

            if ($userCart === null) {
                DB::table('carts')
                    ->where('MaGioHang', $guestCart->MaGioHang)
                    ->update([
                        'MaNguoiDung' => $userId,
                        'KhoaPhien' => null,
                        'NgayCapNhat' => $now,
                    ]);

                return;
            }

            $guestItems = DB::table('cart_items')
                ->where('MaGioHang', $guestCart->MaGioHang)
                ->get();

            foreach ($guestItems as $guestItem) {
                $product = DB::table('products')
                    ->select(['MaSanPham', 'SoLuongTon', 'TrangThai', 'NgayXoa'])
                    ->where('MaSanPham', $guestItem->MaSanPham)
                    ->first();

                if ($product === null || $product->NgayXoa !== null || $product->TrangThai !== 'active') {
                    continue;
                }

                $maxStock = (int) $product->SoLuongTon;
                if ($maxStock <= 0) {
                    continue;
                }

                $existingItem = DB::table('cart_items')
                    ->where('MaGioHang', $userCart->MaGioHang)
                    ->where('MaSanPham', $guestItem->MaSanPham)
                    ->first();

                if ($existingItem !== null) {
                    $mergedQty = min($maxStock, (int) $existingItem->SoLuong + (int) $guestItem->SoLuong);
                    DB::table('cart_items')
                        ->where('MaChiTietGioHang', $existingItem->MaChiTietGioHang)
                        ->update([
                            'SoLuong' => $mergedQty,
                            'NgayCapNhat' => $now,
                        ]);
                } else {
                    $initialQty = min($maxStock, (int) $guestItem->SoLuong);
                    DB::table('cart_items')->insert([
                        'MaGioHang' => $userCart->MaGioHang,
                        'MaSanPham' => $guestItem->MaSanPham,
                        'SoLuong' => $initialQty,
                        'NgayTao' => $now,
                        'NgayCapNhat' => $now,
                    ]);
                }
            }

            DB::table('carts')->where('MaGioHang', $guestCart->MaGioHang)->delete();
        });
    }

    /**
     * Tạo token khôi phục mật khẩu và gửi liên kết đặt lại mật khẩu qua email.
     * - Khi PASSWORD_RESET_DEMO=false (mặc định): Trả về thông báo chung cho mọi trường hợp
     *   để chống dò tìm email (User Enumeration).
     * - Khi PASSWORD_RESET_DEMO=true: Giữ nguyên thông báo chi tiết và trả về link đặt lại mật khẩu.
     *
     * @return array{status: string, message: string, demo_reset_url?: string}
     */
    public function sendPasswordResetEmail(string $email): array
    {
        $normalizedEmail = mb_strtolower(trim($email));
        $isDemoMode = (bool) config('storefront.password_reset_demo', env('PASSWORD_RESET_DEMO', false));
        $genericMessage = 'Nếu email tồn tại trong hệ thống, chúng tôi đã gửi hướng dẫn đặt lại mật khẩu.';

        $user = DB::table('users')
            ->where('Email', $normalizedEmail)
            ->whereNull('NgayXoa')
            ->first();

        if ($user === null) {
            if ($isDemoMode) {
                return [
                    'status' => 'not_found',
                    'message' => 'Không tìm thấy tài khoản nào ứng với địa chỉ email này.',
                ];
            }

            return [
                'status' => 'sent',
                'message' => $genericMessage,
            ];
        }

        if ($user->TrangThai === 'locked') {
            if ($isDemoMode) {
                return [
                    'status' => 'locked',
                    'message' => 'Tài khoản này đang bị khóa, không thể đặt lại mật khẩu.',
                ];
            }

            return [
                'status' => 'sent',
                'message' => $genericMessage,
            ];
        }

        $rawToken = Str::random(64);
        $now = Carbon::now();

        DB::table('password_reset_tokens')->updateOrInsert(
            ['Email' => $user->Email],
            [
                'Token' => Hash::make($rawToken),
                'NgayTao' => $now,
            ]
        );

        $resetUrl = route('password.reset', [
            'token' => $rawToken,
            'email' => $user->Email,
        ]);

        $mailBody = sprintf(
            "Xin chào %s,\n\n" .
            "Chúng tôi nhận được yêu cầu đặt lại mật khẩu cho tài khoản của bạn tại Chợ Figure & Thẻ Bài.\n" .
            "Vui lòng nhấn vào đường dẫn dưới đây để đặt lại mật khẩu mới (có hiệu lực trong %d phút):\n\n" .
            "%s\n\n" .
            "Nếu bạn không yêu cầu đặt lại mật khẩu, vui lòng bỏ qua email này.",
            $user->HoTen,
            self::RESET_TOKEN_TTL_MINUTES,
            $resetUrl
        );

        $mailSent = false;

        try {
            Mail::raw($mailBody, function ($message) use ($user): void {
                $message->to($user->Email, $user->HoTen)
                    ->subject('[Chợ Figure & Thẻ Bài] Yêu cầu đặt lại mật khẩu');
            });
            $mailSent = true;
        } catch (Throwable $exception) {
            Log::error('Không thể gửi email đặt lại mật khẩu: ' . $exception->getMessage(), [
                'email' => $user->Email,
            ]);

            if (!$isDemoMode) {
                return [
                    'status' => 'mail_error',
                    'message' => 'Hệ thống chưa thể gửi email khôi phục mật khẩu (vui lòng kiểm tra cấu hình SMTP).',
                ];
            }
        }

        if ($isDemoMode) {
            return [
                'status' => 'sent',
                'message' => $mailSent
                    ? 'Đã tạo yêu cầu đặt lại mật khẩu (Chế độ Demo đang bật).'
                    : 'Chưa cấu hình SMTP nhưng Chế độ Demo đang bật: bạn có thể dùng trực tiếp liên kết bên dưới.',
                'demo_reset_url' => $resetUrl,
            ];
        }

        return [
            'status' => 'sent',
            'message' => $genericMessage,
        ];
    }

    /**
     * Xác minh token và cập nhật mật khẩu mới cho người dùng.
     *
     * @return array{status: string, message: string}
     */
    public function resetPassword(string $email, string $rawToken, string $newPassword): array
    {
        $normalizedEmail = mb_strtolower(trim($email));

        $tokenRecord = DB::table('password_reset_tokens')
            ->where('Email', $normalizedEmail)
            ->first();

        if ($tokenRecord === null) {
            return [
                'status' => 'invalid_token',
                'message' => 'Yêu cầu đặt lại mật khẩu không tồn tại hoặc đã được sử dụng.',
            ];
        }

        $createdAt = Carbon::parse((string) $tokenRecord->NgayTao);
        if ($createdAt->addMinutes(self::RESET_TOKEN_TTL_MINUTES)->isPast()) {
            DB::table('password_reset_tokens')->where('Email', $normalizedEmail)->delete();

            return [
                'status' => 'expired_token',
                'message' => 'Mã khôi phục mật khẩu đã hết hạn. Vui lòng gửi lại yêu cầu mới.',
            ];
        }

        if (!Hash::check($rawToken, (string) $tokenRecord->Token)) {
            return [
                'status' => 'invalid_token',
                'message' => 'Mã khôi phục mật khẩu không hợp lệ.',
            ];
        }

        $user = DB::table('users')
            ->where('Email', $normalizedEmail)
            ->whereNull('NgayXoa')
            ->first();

        if ($user === null || $user->TrangThai === 'locked') {
            return [
                'status' => 'invalid_user',
                'message' => 'Tài khoản không hợp lệ hoặc đang bị khóa.',
            ];
        }

        DB::transaction(function () use ($user, $normalizedEmail, $newPassword): void {
            DB::table('users')
                ->where('MaNguoiDung', $user->MaNguoiDung)
                ->update([
                    'MatKhau' => Hash::make($newPassword),
                    'MaGhiNho' => Str::random(60),
                    'NgayCapNhat' => Carbon::now(),
                ]);

            DB::table('password_reset_tokens')
                ->where('Email', $normalizedEmail)
                ->delete();
        });

        return [
            'status' => 'ok',
            'message' => 'Đặt lại mật khẩu thành công. Bạn có thể đăng nhập bằng mật khẩu mới.',
        ];
    }
}

