<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Xác thực dữ liệu Đổi mật khẩu của tài khoản cá nhân (A7).
 */
class ChangePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && !$this->user()->isLocked();
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'MatKhauHienTai' => ['required', 'string', 'min:1', 'max:72'],
            'MatKhau' => ['required', 'string', 'min:8', 'max:72', 'confirmed', 'different:MatKhauHienTai'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'MatKhauHienTai.required' => 'Vui lòng nhập mật khẩu hiện tại.',
            'MatKhau.required' => 'Vui lòng nhập mật khẩu mới.',
            'MatKhau.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'MatKhau.max' => 'Mật khẩu mới tối đa 72 ký tự.',
            'MatKhau.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
            'MatKhau.different' => 'Mật khẩu mới phải khác mật khẩu hiện tại.',
        ];
    }
}
