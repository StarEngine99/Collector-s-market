<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Xác thực dữ liệu tạo/cập nhật Chương trình khuyến mãi theo sản phẩm (B5 - bảng `promotions`).
 */
class PromotionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('coupons.manage') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'TenKhuyenMai' => ['required', 'string', 'min:3', 'max:180'],
            'MoTa' => ['nullable', 'string', 'max:2000'],
            'LoaiGiamGia' => ['required', Rule::in(['percent', 'fixed'])],
            'GiaTriGiam' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'BatDauLuc' => ['required', 'date'],
            'KetThucLuc' => ['required', 'date'],
            'TrangThai' => ['required', Rule::in(['scheduled', 'active', 'inactive'])],
            'san_pham_ids' => ['required', 'array', 'min:1'],
            'san_pham_ids.*' => [
                'integer',
                Rule::exists('products', 'MaSanPham')->whereNull('NgayXoa'),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $type = $this->input('LoaiGiamGia');
            $value = (float) $this->input('GiaTriGiam', 0);

            if ($type === 'percent' && $value > 100) {
                $validator->errors()->add('GiaTriGiam', 'Mức giảm theo phần trăm không được vượt quá 100%.');
            }

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
            'TenKhuyenMai.required' => 'Vui lòng nhập tên chương trình khuyến mãi.',
            'TenKhuyenMai.min' => 'Tên khuyến mãi phải có ít nhất 3 ký tự.',
            'TenKhuyenMai.max' => 'Tên khuyến mãi tối đa 180 ký tự.',
            'MoTa.max' => 'Mô tả tối đa 2000 ký tự.',
            'LoaiGiamGia.required' => 'Vui lòng chọn loại giảm giá.',
            'LoaiGiamGia.in' => 'Loại giảm giá không hợp lệ.',
            'GiaTriGiam.required' => 'Vui lòng nhập giá trị giảm.',
            'GiaTriGiam.numeric' => 'Giá trị giảm phải là số.',
            'GiaTriGiam.gt' => 'Giá trị giảm phải lớn hơn 0.',
            'BatDauLuc.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'BatDauLuc.date' => 'Thời gian bắt đầu không đúng định dạng.',
            'KetThucLuc.required' => 'Vui lòng chọn thời gian kết thúc.',
            'KetThucLuc.date' => 'Thời gian kết thúc không đúng định dạng.',
            'TrangThai.in' => 'Trạng thái khuyến mãi không hợp lệ.',
            'san_pham_ids.required' => 'Vui lòng chọn ít nhất 1 sản phẩm áp dụng khuyến mãi.',
            'san_pham_ids.min' => 'Vui lòng chọn ít nhất 1 sản phẩm áp dụng khuyến mãi.',
            'san_pham_ids.*.exists' => 'Sản phẩm được chọn không tồn tại hoặc đã bị xóa.',
        ];
    }
}

