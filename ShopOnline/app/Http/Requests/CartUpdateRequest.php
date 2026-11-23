<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Xác thực dữ liệu khi Cập nhật số lượng sản phẩm trong giỏ hàng (A5).
 */
class CartUpdateRequest extends FormRequest
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
            'SoLuong' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'SoLuong.required' => 'Vui lòng nhập số lượng.',
            'SoLuong.integer' => 'Số lượng phải là số nguyên.',
            'SoLuong.min' => 'Số lượng tối thiểu là 1.',
            'SoLuong.max' => 'Số lượng tối đa là 999.',
        ];
    }
}
