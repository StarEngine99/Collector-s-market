<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminProductManagementTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['view', 'create', 'update', 'delete'] as $action) {
            Gate::define('products.' . $action, static fn (User $user): bool => true);
        }

        Storage::fake('public');
    }

    public function test_manager_can_create_update_and_hide_a_product_with_inventory_history(): void
    {
        $manager = $this->createManager();
        $categoryId = $this->createCategory();
        $sku = 'PRODUCT-' . strtoupper(uniqid());
        $imageContents = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScL6WQAAAABJRU5ErkJggg==');

        $this->actingAs($manager)
            ->post(route('admin.products.store'), [
                'MaDanhMuc' => $categoryId,
                'MaSKU' => $sku,
                'TenSanPham' => 'Collector Figure',
                'MoTa' => 'A sample product',
                'Gia' => 125000,
                'SoLuongTon' => 4,
                'TrangThai' => 'active',
                'NoiBat' => 1,
                'HinhAnh' => [
                    UploadedFile::fake()->createWithContent('front.png', $imageContents),
                ],
            ])
            ->assertRedirect(route('admin.products.index'));

        $product = DB::table('products')->where('MaSKU', $sku)->first();
        $this->assertNotNull($product);
        $this->assertSame('collector-figure', $product->DuongDan);
        $this->assertSame('active', $product->TrangThai);
        $this->assertNotNull($product->NgayDang);

        $image = DB::table('product_images')->where('MaSanPham', $product->MaSanPham)->first();
        $this->assertNotNull($image);
        $this->assertSame($imageContents, $image->DuLieuAnh);
        $this->assertSame('image/png', $image->LoaiNoiDung);
        $this->assertSame('product-images/' . $image->MaHinhAnh, $image->DuongDanHinhAnh);
        Storage::disk('public')->assertDirectoryEmpty('products');
        $this->get(route('product-images.show', $image->MaHinhAnh))
            ->assertOk()
            ->assertHeader('Content-Type', 'image/png')
            ->assertContent($imageContents);

        $this->assertDatabaseHas('inventory_movements', [
            'MaSanPham' => $product->MaSanPham,
            'MaNguoiThucHien' => $manager->getKey(),
            'ThayDoiSoLuong' => 4,
            'LoaiThamChieu' => 'product_creation',
        ]);

        $this->actingAs($manager)
            ->put(route('admin.products.update', $product->MaSanPham), [
                'MaDanhMuc' => $categoryId,
                'MaSKU' => $sku,
                'TenSanPham' => 'Collector Figure',
                'MoTa' => 'Updated product',
                'Gia' => 130000,
                'SoLuongTon' => 9,
                'TrangThai' => 'active',
                'NoiBat' => 1,
                'XoaHinhAnh' => [$image->MaHinhAnh],
            ])
            ->assertRedirect(route('admin.products.edit', $product->MaSanPham));

        $this->assertDatabaseHas('inventory_movements', [
            'MaSanPham' => $product->MaSanPham,
            'MaNguoiThucHien' => $manager->getKey(),
            'ThayDoiSoLuong' => 5,
            'LoaiThamChieu' => 'admin_adjustment',
        ]);
        $this->assertDatabaseMissing('product_images', ['MaHinhAnh' => $image->MaHinhAnh]);

        $this->actingAs($manager)
            ->delete(route('admin.products.destroy', $product->MaSanPham))
            ->assertRedirect(route('admin.products.index'));

        $this->assertDatabaseHas('products', [
            'MaSanPham' => $product->MaSanPham,
            'TrangThai' => 'hidden',
        ]);
        $this->assertNotNull(DB::table('products')->where('MaSanPham', $product->MaSanPham)->value('NgayXoa'));
    }

    public function test_product_list_requires_the_view_permission(): void
    {
        Gate::define('products.view', static fn (User $user): bool => false);

        $this->actingAs($this->createManager())
            ->get(route('admin.products.index'))
            ->assertForbidden();
    }

    public function test_product_creation_rejects_a_duplicate_sku(): void
    {
        $manager = $this->createManager();
        $categoryId = $this->createCategory();
        $sku = 'DUPLICATE-' . strtoupper(uniqid());
        $payload = [
            'MaDanhMuc' => $categoryId,
            'MaSKU' => $sku,
            'TenSanPham' => 'Collector Figure',
            'Gia' => 125000,
            'SoLuongTon' => 0,
            'TrangThai' => 'draft',
        ];

        $this->actingAs($manager)
            ->post(route('admin.products.store'), $payload)
            ->assertRedirect(route('admin.products.index'));

        $this->actingAs($manager)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $payload)
            ->assertSessionHasErrors('MaSKU');

        $payload['MaSKU'] = strtolower($sku);

        $this->actingAs($manager)
            ->from(route('admin.products.create'))
            ->post(route('admin.products.store'), $payload)
            ->assertSessionHasErrors('MaSKU');
    }

    private function createManager(): User
    {
        return User::query()->create([
            'HoTen' => 'Catalog Manager',
            'Email' => 'catalog_' . uniqid() . '@example.com',
            'SoDienThoai' => '0901112233',
            'MatKhau' => Hash::make('Password123'),
            'TrangThai' => 'active',
        ]);
    }

    private function createCategory(): int
    {
        return (int) DB::table('categories')->insertGetId([
            'TenDanhMuc' => 'Figures ' . uniqid(),
            'DuongDan' => 'figures-' . uniqid(),
            'TrangThai' => 'active',
        ]);
    }
}
