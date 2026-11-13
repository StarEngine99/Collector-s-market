<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Xác thực dữ liệu form Đăng ký tài khoản (A6).
 */
class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'HoTen' => ['required', 'string', 'min:2', 'max:150'],
            'Email' => [
                'required',
                'string',
                'email:rfc',
                'max:190',
                Rule::unique('users', 'Email'),
            ],
            'SoDienThoai' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s().]{8,20}$/'],
            'MatKhau' => ['required', 'string', 'min:8', 'max:72', 'confirmed'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'HoTen.required' => 'Vui lòng nhập họ và tên.',
            'HoTen.min' => 'Họ và tên phải có ít nhất 2 ký tự.',
            'HoTen.max' => 'Họ và tên tối đa 150 ký tự.',
            'Email.required' => 'Vui lòng nhập địa chỉ email.',
            'Email.email' => 'Địa chỉ email không đúng định dạng.',
            'Email.max' => 'Địa chỉ email tối đa 190 ký tự.',
            'Email.unique' => 'Địa chỉ email này đã được đăng ký.',
            'SoDienThoai.max' => 'Số điện thoại tối đa 30 ký tự.',
            'SoDienThoai.regex' => 'Số điện thoại không hợp lệ (gồm 8–20 chữ số).',
            'MatKhau.required' => 'Vui lòng nhập mật khẩu.',
            'MatKhau.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'MatKhau.max' => 'Mật khẩu tối đa 72 ký tự.',
            'MatKhau.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];
    }
}

