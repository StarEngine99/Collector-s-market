<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Xác thực dữ liệu tạo/cập nhật bài viết (B6).
 */
class PostRequest extends FormRequest
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
        $postId = $this->route('id');

        return [
            'TieuDe' => ['required', 'string', 'min:3', 'max:200'],
            'DuongDan' => [
                'required',
                'string',
                'max:220',
                'regex:/^[a-z0-9-]+$/',
                Rule::unique('posts', 'DuongDan')->ignore($postId, 'MaBaiViet'),
            ],
            'NoiDung' => ['required', 'string', 'min:10'],
            'TrangThai' => ['required', Rule::in(['draft', 'published', 'hidden'])],
            'NgayDang' => ['nullable', 'date'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'TieuDe.required' => 'Vui lòng nhập tiêu đề bài viết.',
            'TieuDe.min' => 'Tiêu đề phải có ít nhất 3 ký tự.',
            'TieuDe.max' => 'Tiêu đề tối đa 200 ký tự.',
            'DuongDan.required' => 'Vui lòng nhập đường dẫn (slug).',
            'DuongDan.regex' => 'Đường dẫn chỉ gồm chữ thường không dấu, số và dấu gạch ngang.',
            'DuongDan.unique' => 'Đường dẫn bài viết đã tồn tại.',
            'NoiDung.required' => 'Vui lòng nhập nội dung bài viết.',
            'NoiDung.min' => 'Nội dung bài viết phải có ít nhất 10 ký tự.',
            'TrangThai.in' => 'Trạng thái bài viết không hợp lệ.',
            'NgayDang.date' => 'Ngày đăng không đúng định dạng.',
        ];
    }
}
