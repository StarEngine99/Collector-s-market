<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Xác thực dữ liệu cập nhật Thông tin cá nhân (A7).
 */
class UpdateProfileRequest extends FormRequest
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
            'HoTen' => ['required', 'string', 'min:2', 'max:150'],
            'SoDienThoai' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s().]{8,20}$/'],
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
            'SoDienThoai.max' => 'Số điện thoại tối đa 30 ký tự.',
            'SoDienThoai.regex' => 'Số điện thoại không hợp lệ (gồm 8–20 chữ số).',
        ];
    }
}

