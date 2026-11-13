<?php

namespace App\Http\Requests\Admin;

use App\Support\OrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Xác thực dữ liệu khi Quản trị viên / Nhân viên đơn hàng cập nhật trạng thái đơn hàng (B3).
 */
class OrderStatusUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('orders.update-status') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'TrangThai' => [
                'required',
                'string',
                Rule::in(array_keys(OrderStatus::labels())),
            ],
            'ly_do_huy' => ['nullable', 'string', 'max:300'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'TrangThai.required' => 'Vui lòng chọn trạng thái đơn hàng.',
            'TrangThai.in' => 'Trạng thái đơn hàng không hợp lệ.',
            'ly_do_huy.max' => 'Lý do hủy đơn tối đa 300 ký tự.',
        ];
    }
}
