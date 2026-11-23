<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Xác thực dữ liệu khi Thêm sản phẩm vào giỏ hàng (A5).
 * Khớp với form trong resources/views/products/show.blade.php của TV2 (MaSanPham, SoLuong).
 */
class CartAddRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'MaSanPham' => [
                'required',
                'integer',
                Rule::exists('products', 'MaSanPham')->whereNull('NgayXoa'),
            ],
            'SoLuong' => ['required', 'integer', 'min:1', 'max:999'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'MaSanPham.required' => 'Vui lòng chọn sản phẩm.',
            'MaSanPham.exists' => 'Sản phẩm không tồn tại hoặc đã ngừng kinh doanh.',
            'SoLuong.required' => 'Vui lòng nhập số lượng sản phẩm.',
            'SoLuong.integer' => 'Số lượng sản phẩm phải là số nguyên.',
            'SoLuong.min' => 'Số lượng tối thiểu là 1.',
            'SoLuong.max' => 'Số lượng tối đa cho mỗi lần thêm là 999.',
        ];
    }
}
