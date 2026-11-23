<?php

namespace App\Http\Requests\Account;

use Illuminate\Foundation\Http\FormRequest;

class AddressRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && ! $this->user()->isLocked();
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'TenNguoiNhan' => ['required', 'string', 'min:2', 'max:150'],
            'SoDienThoai' => ['required', 'string', 'max:30', 'regex:/^[0-9+\-\s().]{8,20}$/'],
            'TinhThanh' => ['required', 'string', 'min:2', 'max:120'],
            'QuanHuyen' => ['required', 'string', 'min:2', 'max:120'],
            'PhuongXa' => ['required', 'string', 'min:2', 'max:120'],
            'DiaChiChiTiet' => ['required', 'string', 'min:3', 'max:255'],
            'MacDinh' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'TenNguoiNhan.required' => 'Vui lòng nhập họ tên người nhận.',
            'SoDienThoai.required' => 'Vui lòng nhập số điện thoại người nhận.',
            'SoDienThoai.regex' => 'Số điện thoại không hợp lệ.',
            'TinhThanh.required' => 'Vui lòng nhập tỉnh hoặc thành phố.',
            'QuanHuyen.required' => 'Vui lòng nhập quận hoặc huyện.',
            'PhuongXa.required' => 'Vui lòng nhập phường hoặc xã.',
            'DiaChiChiTiet.required' => 'Vui lòng nhập địa chỉ chi tiết.',
            'DiaChiChiTiet.min' => 'Địa chỉ chi tiết phải có ít nhất 3 ký tự.',
        ];
    }
}
