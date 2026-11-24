<?php

namespace Tests\Feature;

use App\Services\ProductPresentationService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductPresentationServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('categories', function (Blueprint $table): void {
            $table->bigIncrements('MaDanhMuc');
            $table->string('TenDanhMuc');
            $table->string('DuongDan');
            $table->string('TrangThai');
            $table->timestamp('NgayXoa')->nullable();
        });
        Schema::create('products', function (Blueprint $table): void {
            $table->bigIncrements('MaSanPham');
            $table->unsignedBigInteger('MaDanhMuc');
            $table->string('MaSKU');
            $table->string('TenSanPham');
            $table->string('DuongDan');
            $table->text('MoTa')->nullable();
            $table->decimal('Gia', 12, 2);
            $table->unsignedInteger('SoLuongTon');
            $table->string('TrangThai');
            $table->boolean('NoiBat');
            $table->timestamp('NgayDang')->nullable();
            $table->timestamp('NgayXoa')->nullable();
        });
        Schema::create('product_images', function (Blueprint $table): void {
            $table->bigIncrements('MaHinhAnh');
            $table->unsignedBigInteger('MaSanPham');
            $table->string('DuongDanHinhAnh');
            $table->string('MoTaHinhAnh')->nullable();
            $table->unsignedInteger('ThuTuSapXep');
        });
        Schema::create('promotion_products', function (Blueprint $table): void {
            $table->unsignedBigInteger('MaSanPham');
            $table->unsignedBigInteger('MaKhuyenMai');
        });
        Schema::create('promotions', function (Blueprint $table): void {
            $table->bigIncrements('MaKhuyenMai');
            $table->string('LoaiGiamGia');
            $table->decimal('GiaTriGiam', 12, 2);
            $table->string('TrangThai');
            $table->timestamp('BatDauLuc');
            $table->timestamp('KetThucLuc');
        });
        Schema::create('orders', function (Blueprint $table): void {
            $table->bigIncrements('MaDonHang');
            $table->string('TrangThai');
        });
        Schema::create('order_items', function (Blueprint $table): void {
            $table->unsignedBigInteger('MaDonHang');
            $table->unsignedBigInteger('MaSanPham')->nullable();
            $table->unsignedInteger('SoLuong');
        });
        Schema::create('product_reviews', function (Blueprint $table): void {
            $table->bigIncrements('MaDanhGia');
            $table->unsignedBigInteger('MaSanPham');
            $table->unsignedTinyInteger('SoSao');
            $table->string('TrangThai');
        });
    }

    public function test_public_product_stats_do_not_invent_sales_or_ratings_and_mark_products_published_within_two_days(): void
    {
        $categoryId = (int) DB::table('categories')->insertGetId([
            'TenDanhMuc' => 'Test category '.uniqid(),
            'DuongDan' => 'test-category-'.uniqid(),
            'TrangThai' => 'active',
        ]);
        $now = Carbon::now();
        $productIds = [];

        foreach ([47, 49] as $hoursAgo) {
            $productIds[$hoursAgo] = (int) DB::table('products')->insertGetId([
                'MaDanhMuc' => $categoryId,
                'MaSKU' => 'RECENT-'.$hoursAgo.'-'.uniqid(),
                'TenSanPham' => 'Recent product '.$hoursAgo,
                'DuongDan' => 'recent-product-'.$hoursAgo.'-'.uniqid(),
                'Gia' => 100000,
                'SoLuongTon' => 1,
                'TrangThai' => 'active',
                'NoiBat' => false,
                'NgayDang' => $now->copy()->subHours($hoursAgo),
            ]);
        }
        $completedOrderId = (int) DB::table('orders')->insertGetId(['TrangThai' => 'completed']);
        $cancelledOrderId = (int) DB::table('orders')->insertGetId(['TrangThai' => 'cancelled']);
        DB::table('order_items')->insert([
            ['MaDonHang' => $completedOrderId, 'MaSanPham' => $productIds[49], 'SoLuong' => 2],
            ['MaDonHang' => $cancelledOrderId, 'MaSanPham' => $productIds[49], 'SoLuong' => 9],
        ]);

        /** @var ProductPresentationService $presentation */
        $presentation = app(ProductPresentationService::class);
        $products = $presentation->enrichProducts(
            $presentation->basePublicProductQuery()
                ->whereIn('products.MaSanPham', array_values($productIds))
                ->get()
        )->keyBy('MaSanPham');

        $recent = $products->get($productIds[47]);
        $this->assertSame(0, $recent->DaBan);
        $this->assertSame(0, $recent->SoDanhGia);
        $this->assertNull($recent->DiemDanhGia);
        $this->assertTrue($recent->LaHangMoi);
        $older = $products->get($productIds[49]);
        $this->assertFalse($older->LaHangMoi);
        $this->assertSame(2, $older->DaBan);
    }
}
