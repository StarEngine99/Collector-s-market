<?php

namespace App\Http\Controllers;

use App\Http\Requests\Auth\ForgotPasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;
use App\Services\AuthService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Controller xử lý Đăng ký, Đăng nhập, Đăng xuất và Quên mật khẩu (A6).
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

    /**
     * Hiển thị trang Đăng nhập.
     */
    public function showLoginForm(): View
    {
        return view('auth.login');
    }

    /**
     * Xử lý yêu cầu Đăng nhập.
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        $validated = $request->validated();
        $guestSessionKey = $request->session()->getId();

        $result = $this->authService->attemptLogin(
            (string) $validated['Email'],
            (string) $validated['MatKhau'],
            (bool) ($validated['ghi_nho'] ?? false),
            $guestSessionKey
        );

        if ($result['status'] !== 'ok') {
            return back()
                ->withInput($request->only('Email', 'ghi_nho'))
                ->withErrors(['Email' => $result['message'] ?? 'Đăng nhập thất bại.']);
        }

        $request->session()->regenerate();

        return redirect()
            ->intended(route('home'))
            ->with('success', 'Đăng nhập thành công! Chào mừng ' . ($result['user']->HoTen ?? 'bạn') . ' quay trở lại.');
    }

    /**
     * Hiển thị trang Đăng ký tài khoản.
     */
    public function showRegisterForm(): View
    {
        return view('auth.register');
    }

    /**
     * Xử lý yêu cầu Đăng ký tài khoản mới.
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $guestSessionKey = $request->session()->getId();
        $userId = $this->authService->register($request->validated(), $guestSessionKey);

        Auth::loginUsingId($userId);
        $request->session()->regenerate();

        return redirect()
            ->intended(route('home'))
            ->with('success', 'Đăng ký tài khoản thành công!');
    }

    /**
     * Xử lý Đăng xuất và hủy phiên hiện tại.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()
            ->route('home')
            ->with('success', 'Bạn đã đăng xuất khỏi hệ thống.');
    }

    /**
     * Hiển thị trang Quên mật khẩu.
     */
    public function showForgotPasswordForm(): View
    {
        return view('auth.forgot-password');
    }

    /**
     * Xử lý gửi liên kết đặt lại mật khẩu qua email.
     */
    public function sendResetLinkEmail(ForgotPasswordRequest $request): RedirectResponse
    {
        $result = $this->authService->sendPasswordResetEmail((string) $request->validated('Email'));

        if ($result['status'] !== 'sent') {
            return back()
                ->withInput()
                ->with('error', $result['message']);
        }

        $redirect = back()->with('success', $result['message']);

        if (!empty($result['demo_reset_url'])) {
            $redirect->with('demo_reset_url', $result['demo_reset_url']);
        }

        return $redirect;
    }

    /**
     * Hiển thị trang Đặt lại mật khẩu mới.
     */
    public function showResetPasswordForm(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    /**
     * Xử lý cập nhật mật khẩu mới từ liên kết khôi phục.
     */
    public function resetPassword(ResetPasswordRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $result = $this->authService->resetPassword(
            (string) $validated['Email'],
            (string) $validated['token'],
            (string) $validated['MatKhau']
        );

        if ($result['status'] !== 'ok') {
            return back()
                ->withInput($request->only('Email'))
                ->with('error', $result['message']);
        }

        return redirect()
            ->route('login')
            ->with('success', $result['message']);
    }
}

