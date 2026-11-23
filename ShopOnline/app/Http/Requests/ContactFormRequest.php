<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Xác thực dữ liệu form liên hệ khách hàng (A8).
 */
class ContactFormRequest extends FormRequest
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
            'ho_ten' => ['required', 'string', 'min:2', 'max:150'],
            'email' => ['required', 'email:rfc', 'max:190'],
            'so_dien_thoai' => ['nullable', 'string', 'max:30', 'regex:/^[0-9+\-\s().]{8,20}$/'],
            'chu_de' => ['required', 'string', 'min:3', 'max:200'],
            'noi_dung' => ['required', 'string', 'min:10', 'max:3000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'ho_ten.required' => 'Vui lòng nhập họ tên.',
            'ho_ten.min' => 'Họ tên phải có ít nhất 2 ký tự.',
            'ho_ten.max' => 'Họ tên tối đa 150 ký tự.',
            'email.required' => 'Vui lòng nhập địa chỉ email.',
            'email.email' => 'Địa chỉ email không đúng định dạng.',
            'so_dien_thoai.regex' => 'Số điện thoại không hợp lệ.',
            'chu_de.required' => 'Vui lòng nhập chủ đề liên hệ.',
            'chu_de.min' => 'Chủ đề phải có ít nhất 3 ký tự.',
            'noi_dung.required' => 'Vui lòng nhập nội dung liên hệ.',
            'noi_dung.min' => 'Nội dung liên hệ phải có ít nhất 10 ký tự.',
            'noi_dung.max' => 'Nội dung liên hệ tối đa 3000 ký tự.',
        ];
    }
}
