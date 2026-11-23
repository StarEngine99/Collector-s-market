<?php

namespace App\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class ProductManagementService
{
    public function paginateProducts(
        string $keyword = '',
        ?int $categoryId = null,
        ?string $status = null,
        ?string $stock = null,
        int $perPage = 15
    ): LengthAwarePaginator {
        $query = DB::table('products')
            ->join('categories', 'categories.MaDanhMuc', '=', 'products.MaDanhMuc')
            ->select([
                'products.MaSanPham',
                'products.MaDanhMuc',
                'products.MaSKU',
                'products.TenSanPham',
                'products.Gia',
                'products.SoLuongTon',
                'products.TrangThai',
                'products.NoiBat',
                'categories.TenDanhMuc',
            ])
            ->addSelect([
                'DuongDanHinhAnh' => DB::table('product_images')
                    ->select('DuongDanHinhAnh')
                    ->whereColumn('product_images.MaSanPham', 'products.MaSanPham')
                    ->orderBy('ThuTuSapXep')
                    ->orderBy('MaHinhAnh')
                    ->limit(1),
            ])
            ->whereNull('products.NgayXoa');

        if ($keyword !== '') {
            $query->where(function ($query) use ($keyword): void {
                $query->where('products.TenSanPham', 'like', '%' . $keyword . '%')
                    ->orWhere('products.MaSKU', 'like', '%' . $keyword . '%');
            });
        }

        if ($categoryId !== null) {
            $query->where('products.MaDanhMuc', $categoryId);
        }

        if ($status !== null) {
            $query->where('products.TrangThai', $status);
        }

        if ($stock === 'in_stock') {
            $query->where('products.SoLuongTon', '>', 0);
        } elseif ($stock === 'out_of_stock') {
            $query->where('products.SoLuongTon', '=', 0);
        }

        return $query
            ->orderByDesc('products.NgayCapNhat')
            ->orderByDesc('products.MaSanPham')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * @return \Illuminate\Support\Collection<int, object>
     */
    public function categories(): \Illuminate\Support\Collection
    {
        return DB::table('categories')
            ->select(['MaDanhMuc', 'TenDanhMuc', 'TrangThai'])
            ->whereNull('NgayXoa')
            ->orderBy('ThuTuSapXep')
            ->orderBy('TenDanhMuc')
            ->get();
    }

    /**
     * @return array{product: object, images: \Illuminate\Support\Collection<int, object>}|null
     */
    public function findProduct(int $id): ?array
    {
        $product = DB::table('products')
            ->where('MaSanPham', $id)
            ->whereNull('NgayXoa')
            ->first();

        if ($product === null) {
            return null;
        }

        return [
            'product' => $product,
            'images' => DB::table('product_images')
                ->select(['MaHinhAnh', 'MaSanPham', 'DuongDanHinhAnh', 'MoTaHinhAnh', 'ThuTuSapXep'])
                ->where('MaSanPham', $id)
                ->orderBy('ThuTuSapXep')
                ->orderBy('MaHinhAnh')
                ->get(),
        ];
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, UploadedFile> $images
     */
    public function create(array $data, array $images, ?int $managerId): int
    {
        $sku = $this->normalizeSku((string) ($data['MaSKU'] ?? ''));

        return (int) DB::transaction(function () use ($data, $images, $managerId, $sku): int {
            $now = Carbon::now();
            $stock = (int) $data['SoLuongTon'];
            $status = (string) $data['TrangThai'];
            $id = (int) DB::table('products')->insertGetId([
                'MaDanhMuc' => (int) $data['MaDanhMuc'],
                'MaSKU' => $sku,
                'TenSanPham' => trim((string) $data['TenSanPham']),
                'DuongDan' => $this->uniqueSlug((string) $data['TenSanPham'], (string) $data['MaSKU']),
                'MoTa' => $data['MoTa'] ?? null,
                'Gia' => $data['Gia'],
                'SoLuongTon' => $stock,
                'TrangThai' => $status,
                'NoiBat' => (bool) ($data['NoiBat'] ?? false),
                'NgayDang' => $status === 'active' ? $now : null,
                'NgayTao' => $now,
                'NgayCapNhat' => $now,
            ]);

            if ($stock > 0) {
                $this->recordStockMovement($id, $managerId, $stock, 'Ghi nhận tồn kho ban đầu', 'product_creation');
            }

            $this->storeImages($id, $images, (string) $data['TenSanPham']);

            return $id;
        });
    }

    /**
     * @param array<string, mixed> $data
     * @param array<int, UploadedFile> $images
     */
    public function update(int $id, array $data, array $images, ?int $managerId): bool
    {
        $pathsToDelete = [];
        $sku = $this->normalizeSku((string) ($data['MaSKU'] ?? ''));

        $updated = DB::transaction(function () use ($id, $data, $images, $managerId, &$pathsToDelete, $sku): bool {
            $product = DB::table('products')
                ->where('MaSanPham', $id)
                ->whereNull('NgayXoa')
                ->lockForUpdate()
                ->first();

            if ($product === null) {
                return false;
            }

            $now = Carbon::now();
            $status = (string) $data['TrangThai'];
            $newStock = (int) $data['SoLuongTon'];
            $stockDifference = $newStock - (int) $product->SoLuongTon;

            DB::table('products')
                ->where('MaSanPham', $id)
                ->update([
                    'MaDanhMuc' => (int) $data['MaDanhMuc'],
                    'MaSKU' => $sku,
                    'TenSanPham' => trim((string) $data['TenSanPham']),
                    'MoTa' => $data['MoTa'] ?? null,
                    'Gia' => $data['Gia'],
                    'SoLuongTon' => $newStock,
                    'TrangThai' => $status,
                    'NoiBat' => (bool) ($data['NoiBat'] ?? false),
                    'NgayDang' => $status === 'active' ? ($product->NgayDang ?? $now) : $product->NgayDang,
                    'NgayCapNhat' => $now,
                ]);

            $removeIds = array_map('intval', $data['XoaHinhAnh'] ?? []);
            if ($removeIds !== []) {
                $pathsToDelete = DB::table('product_images')
                    ->where('MaSanPham', $id)
                    ->whereIn('MaHinhAnh', $removeIds)
                    ->pluck('DuongDanHinhAnh')
                    ->all();

                DB::table('product_images')
                    ->where('MaSanPham', $id)
                    ->whereIn('MaHinhAnh', $removeIds)
                    ->delete();
            }

            $this->storeImages($id, $images, (string) $data['TenSanPham']);

            if ($stockDifference !== 0) {
                $this->recordStockMovement(
                    $id,
                    $managerId,
                    $stockDifference,
                    'Điều chỉnh tồn kho qua trang quản trị',
                    'admin_adjustment'
                );
            }

            return true;
        });

        if ($updated) {
            foreach ($pathsToDelete as $path) {
                $this->deletePublicImage((string) $path);
            }
        }

        return $updated;
    }

    public function softDelete(int $id): bool
    {
        $now = Carbon::now();

        // Kiểm tra nếu chưa từng có trong đơn hàng nào thì xóa sạch hoàn toàn
        $hasOrders = DB::table('order_items')->where('MaSanPham', $id)->exists();
        if (!$hasOrders) {
            $images = DB::table('product_images')->where('MaSanPham', $id)->pluck('DuongDanHinhAnh')->all();
            foreach ($images as $img) {
                $this->deletePublicImage((string) $img);
            }
            DB::table('product_images')->where('MaSanPham', $id)->delete();
            DB::table('wishlists')->where('MaSanPham', $id)->delete();
            DB::table('product_reviews')->where('MaSanPham', $id)->delete();
            DB::table('promotion_products')->where('MaSanPham', $id)->delete();
            DB::table('cart_items')->where('MaSanPham', $id)->delete();
            DB::table('inventory_movements')->where('MaSanPham', $id)->delete();
            return DB::table('products')->where('MaSanPham', $id)->delete() > 0;
        }

        return DB::table('products')
            ->where('MaSanPham', $id)
            ->whereNull('NgayXoa')
            ->update([
                'TrangThai' => 'hidden',
                'NgayXoa' => $now,
                'NgayCapNhat' => $now,
            ]) > 0;
    }

    private function normalizeSku(string $sku): string
    {
        return strtoupper(trim($sku));
    }

    private function uniqueSlug(string $name, string $sku): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = Str::slug($sku) ?: 'san-pham';
        }

        $base = substr($base, 0, 200);
        $slug = $base;
        $suffix = 2;

        while (DB::table('products')->where('DuongDan', $slug)->exists()) {
            $suffixText = '-' . $suffix++;
            $slug = substr($base, 0, 220 - strlen($suffixText)) . $suffixText;
        }

        return $slug;
    }

    /**
     * @param array<int, UploadedFile> $images
     */
    private function storeImages(int $productId, array $images, string $productName): void
    {
        $nextOrder = (int) (DB::table('product_images')->where('MaSanPham', $productId)->max('ThuTuSapXep') ?? -1) + 1;

        foreach ($images as $image) {
            $contents = $image->get();
            $mimeType = $image->getMimeType();
            if (!is_string($contents) || $contents === '' || !in_array($mimeType, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                throw new RuntimeException('Không thể đọc nội dung ảnh sản phẩm.');
            }

            $imageId = (int) DB::table('product_images')->insertGetId([
                'MaSanPham' => $productId,
                'DuongDanHinhAnh' => 'product-images/pending',
                'DuLieuAnh' => $contents,
                'LoaiNoiDung' => $mimeType,
                'MoTaHinhAnh' => $productName,
                'ThuTuSapXep' => $nextOrder++,
                'NgayTao' => Carbon::now(),
            ]);

            DB::table('product_images')
                ->where('MaHinhAnh', $imageId)
                ->update(['DuongDanHinhAnh' => 'product-images/' . $imageId]);
        }
    }

    private function recordStockMovement(
        int $productId,
        ?int $managerId,
        int $quantityChange,
        string $reason,
        string $referenceType
    ): void {
        DB::table('inventory_movements')->insert([
            'MaSanPham' => $productId,
            'MaNguoiThucHien' => $managerId,
            'ThayDoiSoLuong' => $quantityChange,
            'LyDo' => $reason,
            'LoaiThamChieu' => $referenceType,
            'MaThamChieu' => $productId,
            'NgayTao' => Carbon::now(),
        ]);
    }

    private function deletePublicImage(string $publicPath): void
    {
        if (!str_starts_with($publicPath, 'storage/products/')) {
            return;
        }

        $path = substr($publicPath, strlen('storage/'));
        if (Storage::disk('public')->exists($path) && !Storage::disk('public')->delete($path)) {
            Log::warning('Không thể xóa ảnh sản phẩm khỏi public disk.', ['path' => $path]);
        }
    }
}
