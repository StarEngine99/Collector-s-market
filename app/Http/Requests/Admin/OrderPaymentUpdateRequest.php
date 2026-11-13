<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Xác thực dữ liệu khi Quản trị viên xác nhận trạng thái thanh toán của đơn hàng (B3).
 */
class OrderPaymentUpdateRequest extends FormRequest
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
            'TrangThaiThanhToan' => [
                'required',
                'string',
                Rule::in(['pending', 'paid']),
            ],
            'MaGiaoDichNhaCungCap' => ['nullable', 'string', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'TrangThaiThanhToan.required' => 'Vui lòng chọn trạng thái thanh toán.',
            'TrangThaiThanhToan.in' => 'Trạng thái thanh toán chỉ nhận Chưa thanh toán (pending) hoặc Đã thanh toán (paid).',
            'MaGiaoDichNhaCungCap.max' => 'Mã giao dịch tham chiếu tối đa 150 ký tự.',
        ];
    }
}
