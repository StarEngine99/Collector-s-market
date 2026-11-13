<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Xác thực dữ liệu form Đăng nhập (A6).
 */
class LoginRequest extends FormRequest
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
            'Email' => ['required', 'string', 'email:rfc', 'max:190'],
            'MatKhau' => ['required', 'string', 'min:1', 'max:72'],
            'ghi_nho' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'Email.required' => 'Vui lòng nhập địa chỉ email.',
            'Email.email' => 'Địa chỉ email không đúng định dạng.',
            'Email.max' => 'Địa chỉ email tối đa 190 ký tự.',
            'MatKhau.required' => 'Vui lòng nhập mật khẩu.',
        ];
    }
}

