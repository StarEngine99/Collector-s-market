<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Xác thực dữ liệu form Yêu cầu đặt lại mật khẩu qua email (A6).
 */
class ForgotPasswordRequest extends FormRequest
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
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'Email.required' => 'Vui lòng nhập địa chỉ email đã đăng ký.',
            'Email.email' => 'Địa chỉ email không đúng định dạng.',
            'Email.max' => 'Địa chỉ email tối đa 190 ký tự.',
        ];
    }
}

