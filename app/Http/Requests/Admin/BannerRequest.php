<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Xác thực dữ liệu tạo/cập nhật banner trang chủ (B6).
 */
class BannerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('content.manage') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $isUpdate = $this->route('id') !== null;

        return [
            'TieuDe' => ['required', 'string', 'min:2', 'max:180'],
            'hinh_anh' => [
                $isUpdate ? 'nullable' : 'required',
                'image',
                'mimes:jpeg,jpg,png,webp',
                'max:2048',
            ],
            'DuongDanDich' => ['nullable', 'string', 'max:500', 'regex:/^(\/|https?:\/\/)/'],
            'ThuTuSapXep' => ['required', 'integer', 'min:0', 'max:9999'],
            'BatDauLuc' => ['nullable', 'date'],
            'KetThucLuc' => ['nullable', 'date'],
            'TrangThai' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $start = $this->input('BatDauLuc');
            $end = $this->input('KetThucLuc');

            if (!empty($start) && !empty($end) && strtotime((string) $end) < strtotime((string) $start)) {
                $validator->errors()->add('KetThucLuc', 'Thời gian kết thúc phải sau hoặc bằng thời gian bắt đầu.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'TieuDe.required' => 'Vui lòng nhập tiêu đề banner.',
            'hinh_anh.required' => 'Vui lòng chọn hình ảnh banner.',
            'hinh_anh.image' => 'Tệp tải lên phải là hình ảnh.',
            'hinh_anh.mimes' => 'Chỉ chấp nhận ảnh định dạng JPEG, JPG, PNG hoặc WEBP.',
            'hinh_anh.max' => 'Kích thước ảnh tối đa 2MB.',
            'DuongDanDich.regex' => 'Đường dẫn đích phải bắt đầu bằng /, http:// hoặc https://.',
            'ThuTuSapXep.required' => 'Vui lòng nhập thứ tự hiển thị.',
            'ThuTuSapXep.integer' => 'Thứ tự hiển thị phải là số nguyên.',
            'ThuTuSapXep.min' => 'Thứ tự hiển thị không được âm.',
            'TrangThai.in' => 'Trạng thái banner không hợp lệ.',
        ];
    }
}
