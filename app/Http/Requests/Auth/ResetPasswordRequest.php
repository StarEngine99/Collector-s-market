<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Xác thực dữ liệu form Đặt lại mật khẩu mới (A6).
 */
class ResetPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'],
            'Email' => ['required', 'string', 'email:rfc', 'max:190'],
            'MatKhau' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Mã khôi phục mật khẩu không hợp lệ.',
            'Email.required' => 'Vui lòng nhập địa chỉ email.',
            'Email.email' => 'Địa chỉ email không đúng định dạng.',
            'MatKhau.required' => 'Vui lòng nhập mật khẩu mới.',
            'MatKhau.min' => 'Mật khẩu mới phải có ít nhất 8 ký tự.',
            'MatKhau.max' => 'Mật khẩu mới tối đa 72 ký tự.',
            'MatKhau.confirmed' => 'Xác nhận mật khẩu mới không khớp.',
        ];
    }
}

