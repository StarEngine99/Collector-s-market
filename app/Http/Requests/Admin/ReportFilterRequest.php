<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Xác thực bộ lọc báo cáo thống kê (B7).
 */
class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('reports.view') ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'period' => ['nullable', Rule::in(['daily', 'monthly'])],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date'],
            'low_stock_threshold' => ['nullable', 'integer', 'min:0', 'max:1000'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $from = $this->input('from_date');
            $to = $this->input('to_date');

            if (!empty($from) && !empty($to) && strtotime((string) $to) < strtotime((string) $from)) {
                $validator->errors()->add('to_date', 'Ngày kết thúc phải lớn hơn hoặc bằng ngày bắt đầu.');
            }
        });
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'period.in' => 'Kỳ thống kê không hợp lệ.',
            'from_date.date' => 'Từ ngày không đúng định dạng.',
            'to_date.date' => 'Đến ngày không đúng định dạng.',
            'low_stock_threshold.integer' => 'Ngưỡng sắp hết hàng phải là số nguyên.',
            'low_stock_threshold.min' => 'Ngưỡng sắp hết hàng không được âm.',
        ];
    }
}
