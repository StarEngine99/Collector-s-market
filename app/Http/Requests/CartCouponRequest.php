<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Xác thực dữ liệu khi Áp dụng mã giảm giá trong giỏ hàng (A5).
 */
class CartCouponRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('MaUuDai')) {
            $this->merge([
                'MaUuDai' => mb_strtoupper(trim((string) $this->input('MaUuDai'))),
            ]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'MaUuDai' => ['required', 'string', 'min:2', 'max:80'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'MaUuDai.required' => 'Vui lòng nhập mã giảm giá.',
            'MaUuDai.min' => 'Mã giảm giá phải có ít nhất 2 ký tự.',
            'MaUuDai.max' => 'Mã giảm giá tối đa 80 ký tự.',
        ];
    }
}
