<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'Email' => mb_strtolower(trim((string) $this->input('Email'))),
        ]);
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $userId = $this->route('id');

        return [
            'HoTen' => ['required', 'string', 'min:2', 'max:150'],
            'Email' => [
                'required',
                'email',
                'max:190',
                Rule::unique('users', 'Email')->ignore($userId, 'MaNguoiDung'),
            ],
            'SoDienThoai' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s().]{8,20}$/'],
            'MatKhau' => [
                $userId === null ? 'required' : 'nullable',
                'string',
                'min:8',
                'confirmed',
            ],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'HoTen.required' => 'Vui lòng nhập họ và tên.',
            'Email.required' => 'Vui lòng nhập email.',
            'Email.email' => 'Email không hợp lệ.',
            'Email.unique' => 'Email này đã được sử dụng.',
            'SoDienThoai.regex' => 'Số điện thoại không hợp lệ.',
            'MatKhau.required' => 'Vui lòng đặt mật khẩu cho tài khoản.',
            'MatKhau.min' => 'Mật khẩu phải có ít nhất 8 ký tự.',
            'MatKhau.confirmed' => 'Xác nhận mật khẩu không khớp.',
        ];
    }
}
