<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Xác thực dữ liệu Đặt hàng / Thanh toán (A5).
 * Chỉ chấp nhận 2 phương thức thanh toán đã chốt: COD ('cod') và Chuyển khoản ('bank_transfer').
 */
class CheckoutRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && !$this->user()->isLocked();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'MaDiaChi' => ['nullable', 'integer'],
            'TenNguoiNhan' => ['required', 'string', 'min:2', 'max:150'],
            'SoDienThoaiNguoiNhan' => [
                'required',
                'string',
                'max:30',
                'regex:/^[0-9+\-\s().]{8,20}$/',
            ],
            'TinhThanh' => ['required', 'string', 'min:2', 'max:120'],
            'QuanHuyen' => ['required', 'string', 'min:2', 'max:120'],
            'PhuongXa' => ['required', 'string', 'min:2', 'max:120'],
            'DiaChiChiTiet' => ['required', 'string', 'min:3', 'max:255'],
            'PhuongThuc' => ['required', Rule::in(['cod', 'bank_transfer'])],
            'GhiChu' => ['nullable', 'string', 'max:1000'],
            'luu_dia_chi' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'TenNguoiNhan.required' => 'Vui lòng nhập họ tên người nhận hàng.',
            'TenNguoiNhan.min' => 'Họ tên người nhận phải có ít nhất 2 ký tự.',
            'TenNguoiNhan.max' => 'Họ tên người nhận tối đa 150 ký tự.',
            'SoDienThoaiNguoiNhan.required' => 'Vui lòng nhập số điện thoại người nhận.',
            'SoDienThoaiNguoiNhan.regex' => 'Số điện thoại người nhận không hợp lệ (gồm 8–20 chữ số).',
            'TinhThanh.required' => 'Vui lòng nhập Tỉnh / Thành phố.',
            'QuanHuyen.required' => 'Vui lòng nhập Quận / Huyện.',
            'PhuongXa.required' => 'Vui lòng nhập Phường / Xã.',
            'DiaChiChiTiet.required' => 'Vui lòng nhập số nhà, tên đường chi tiết.',
            'DiaChiChiTiet.min' => 'Địa chỉ chi tiết phải có ít nhất 3 ký tự.',
            'PhuongThuc.required' => 'Vui lòng chọn phương thức thanh toán.',
            'PhuongThuc.in' => 'Phương thức thanh toán chỉ hỗ trợ COD hoặc Chuyển khoản ngân hàng.',
            'GhiChu.max' => 'Ghi chú đơn hàng tối đa 1000 ký tự.',
        ];
    }
}
