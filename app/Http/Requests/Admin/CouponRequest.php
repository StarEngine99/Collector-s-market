<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Xác thực dữ liệu tạo/cập nhật Mã giảm giá (B5 - bảng `coupons`).
 */
class CouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('coupons.manage') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('MaUuDai')) {
            $this->merge([
                'MaUuDai' => mb_strtoupper(trim((string) $this->input('MaUuDai'))),
            ]);
        }

        if ($this->input('DonHangToiThieu') === null || $this->input('DonHangToiThieu') === '') {
            $this->merge([
                'DonHangToiThieu' => 0,
            ]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $couponId = $this->route('id');

        return [
            'MaUuDai' => [
                'required',
                'string',
                'min:3',
                'max:80',
                'regex:/^[A-Z0-9_-]+$/',
                Rule::unique('coupons', 'MaUuDai')->ignore($couponId, 'MaPhieuGiamGia'),
            ],
            'LoaiGiamGia' => ['required', Rule::in(['percent', 'fixed'])],
            'GiaTriGiam' => ['required', 'numeric', 'gt:0', 'max:9999999999.99'],
            'DonHangToiThieu' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'SoLanSuDungToiDa' => ['nullable', 'integer', 'min:1', 'max:1000000'],
            'SoLanSuDungToiDaMoiNguoi' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'BatDauLuc' => ['required', 'date'],
            'KetThucLuc' => ['required', 'date'],
            'TrangThai' => ['required', Rule::in(['active', 'inactive'])],
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

            $maxUses = $this->input('SoLanSuDungToiDa');
            $maxPerUser = $this->input('SoLanSuDungToiDaMoiNguoi');

            if (
                $maxUses !== null && $maxUses !== '' &&
                $maxPerUser !== null && $maxPerUser !== '' &&
                (int) $maxPerUser > (int) $maxUses
            ) {
                $validator->errors()->add(
                    'SoLanSuDungToiDaMoiNguoi',
                    'Giới hạn mỗi khách hàng không được lớn hơn tổng số lượt sử dụng tối đa.'
                );
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'MaUuDai.required' => 'Vui lòng nhập mã ưu đãi.',
            'MaUuDai.min' => 'Mã ưu đãi phải có ít nhất 3 ký tự.',
            'MaUuDai.max' => 'Mã ưu đãi tối đa 80 ký tự.',
            'MaUuDai.regex' => 'Mã ưu đãi chỉ gồm chữ in hoa không dấu, chữ số, dấu gạch ngang (-) và gạch dưới (_).',
            'MaUuDai.unique' => 'Mã ưu đãi này đã tồn tại.',
            'LoaiGiamGia.required' => 'Vui lòng chọn loại giảm giá.',
            'LoaiGiamGia.in' => 'Loại giảm giá không hợp lệ.',
            'GiaTriGiam.required' => 'Vui lòng nhập giá trị giảm.',
            'GiaTriGiam.numeric' => 'Giá trị giảm phải là số.',
            'GiaTriGiam.gt' => 'Giá trị giảm phải lớn hơn 0.',
            'DonHangToiThieu.numeric' => 'Đơn hàng tối thiểu phải là số.',
            'DonHangToiThieu.min' => 'Đơn hàng tối thiểu không được âm.',
            'SoLanSuDungToiDa.integer' => 'Số lần sử dụng tối đa phải là số nguyên.',
            'SoLanSuDungToiDa.min' => 'Số lần sử dụng tối đa phải từ 1 trở lên.',
            'SoLanSuDungToiDaMoiNguoi.integer' => 'Giới hạn mỗi khách phải là số nguyên.',
            'SoLanSuDungToiDaMoiNguoi.min' => 'Giới hạn mỗi khách phải từ 1 trở lên.',
            'BatDauLuc.required' => 'Vui lòng chọn thời gian bắt đầu.',
            'BatDauLuc.date' => 'Thời gian bắt đầu không đúng định dạng.',
            'KetThucLuc.required' => 'Vui lòng chọn thời gian kết thúc.',
            'KetThucLuc.date' => 'Thời gian kết thúc không đúng định dạng.',
            'TrangThai.in' => 'Trạng thái mã giảm giá không hợp lệ.',
        ];
    }
}

