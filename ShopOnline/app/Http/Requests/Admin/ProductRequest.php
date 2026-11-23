<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('id') !== null ? 'products.update' : 'products.create';

        return $this->user()?->can($permission) ?? false;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function prepareForValidation(): void
    {
        $sku = trim((string) $this->input('MaSKU', ''));

        if ($sku !== '') {
            $this->merge([
                'MaSKU' => strtoupper($sku),
            ]);
        }
    }

    public function rules(): array
    {
        $productId = $this->route('id');

        return [
            'MaDanhMuc' => [
                'required',
                'integer',
                Rule::exists('categories', 'MaDanhMuc')->whereNull('NgayXoa'),
            ],
            'MaSKU' => [
                'required',
                'string',
                'max:80',
                Rule::unique('products', 'MaSKU')->ignore($productId, 'MaSanPham'),
            ],
            'TenSanPham' => ['required', 'string', 'min:2', 'max:200'],
            'MoTa' => ['nullable', 'string', 'max:20000'],
            'Gia' => ['required', 'numeric', 'min:0', 'max:9999999999.99'],
            'SoLuongTon' => ['required', 'integer', 'min:0', 'max:4294967295'],
            'TrangThai' => ['required', Rule::in(['draft', 'active', 'hidden'])],
            'NoiBat' => ['nullable', 'boolean'],
            'HinhAnh' => ['nullable', 'array', 'max:5'],
            'HinhAnh.*' => ['image', 'mimes:jpeg,jpg,png,webp', 'max:5120'],
            'XoaHinhAnh' => ['nullable', 'array', 'max:5'],
            'XoaHinhAnh.*' => ['integer', 'distinct'],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $productId = (int) $this->route('id', 0);
            $removeIds = $this->input('XoaHinhAnh', []);
            $removeIds = is_array($removeIds) ? array_map('intval', $removeIds) : [];
            $images = $this->file('HinhAnh', []);
            $newImageCount = is_array($images) ? count($images) : 0;
            $sku = strtoupper(trim((string) $this->input('MaSKU', '')));

            if ($sku !== '' && $this->skuExists($sku, $productId)) {
                $validator->errors()->add('MaSKU', 'Mã SKU này đã được sử dụng.');
            }

            $existingImageCount = DB::table('product_images')
                ->where('MaSanPham', $productId)
                ->count();
            $validRemoveCount = $removeIds === []
                ? 0
                : DB::table('product_images')
                    ->where('MaSanPham', $productId)
                    ->whereIn('MaHinhAnh', $removeIds)
                    ->count();

            if ($validRemoveCount !== count($removeIds)) {
                $validator->errors()->add('XoaHinhAnh', 'Ảnh cần xóa không thuộc sản phẩm này.');
            }

            if ($existingImageCount - $validRemoveCount + $newImageCount > 5) {
                $validator->errors()->add('HinhAnh', 'Mỗi sản phẩm chỉ được có tối đa 5 ảnh.');
            }
        });
    }

    private function skuExists(string $sku, int $productId): bool
    {
        $query = DB::table('products')
            ->whereRaw('UPPER(TRIM(MaSKU)) = ?', [$sku]);

        if ($productId > 0) {
            $query->where('MaSanPham', '!=', $productId);
        }

        return $query->exists();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'MaDanhMuc.required' => 'Vui lòng chọn danh mục.',
            'MaDanhMuc.exists' => 'Danh mục đã chọn không còn khả dụng.',
            'MaSKU.required' => 'Vui lòng nhập mã SKU.',
            'MaSKU.unique' => 'Mã SKU này đã được sử dụng.',
            'TenSanPham.required' => 'Vui lòng nhập tên sản phẩm.',
            'Gia.required' => 'Vui lòng nhập giá sản phẩm.',
            'Gia.min' => 'Giá sản phẩm không được âm.',
            'SoLuongTon.required' => 'Vui lòng nhập số lượng tồn kho.',
            'SoLuongTon.min' => 'Số lượng tồn kho không được âm.',
            'TrangThai.in' => 'Trạng thái sản phẩm không hợp lệ.',
            'HinhAnh.array' => 'Danh sách ảnh không hợp lệ.',
            'HinhAnh.max' => 'Mỗi lần chỉ được tải lên tối đa 5 ảnh.',
            'HinhAnh.*.image' => 'Tệp tải lên phải là hình ảnh.',
            'HinhAnh.*.mimes' => 'Chỉ chấp nhận ảnh JPEG, JPG, PNG hoặc WEBP.',
            'HinhAnh.*.max' => 'Mỗi ảnh có dung lượng tối đa 5MB.',
            'XoaHinhAnh.*.distinct' => 'Không thể chọn một ảnh nhiều lần để xóa.',
        ];
    }
}
