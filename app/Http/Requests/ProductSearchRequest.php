<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * Xác thực tham số tìm kiếm và lọc sản phẩm (A4).
 */
class ProductSearchRequest extends FormRequest
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
            'q' => ['nullable', 'string', 'max:120'],
            'category' => ['nullable', 'string', 'max:180', 'regex:/^[a-z0-9-]+$/'],
            'min_price' => ['nullable', 'numeric', 'min:0'],
            'max_price' => ['nullable', 'numeric', 'min:0'],
            'sort' => ['nullable', 'in:newest,price_asc,price_desc'],
        ];
    }

    /**
     * Kiểm tra khoảng giá hợp lệ khi người dùng nhập cả giá tối thiểu và tối đa.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $min = $this->input('min_price');
            $max = $this->input('max_price');

            if ($min !== null && $min !== '' && $max !== null && $max !== '' && (float) $max < (float) $min) {
                $validator->errors()->add('max_price', 'Giá tối đa phải lớn hơn hoặc bằng giá tối thiểu.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'q.max' => 'Từ khóa tìm kiếm tối đa 120 ký tự.',
            'category.regex' => 'Danh mục lọc không hợp lệ.',
            'min_price.numeric' => 'Giá tối thiểu phải là số.',
            'min_price.min' => 'Giá tối thiểu không được âm.',
            'max_price.numeric' => 'Giá tối đa phải là số.',
            'max_price.min' => 'Giá tối đa không được âm.',
            'sort.in' => 'Kiểu sắp xếp không hợp lệ.',
        ];
    }
}
