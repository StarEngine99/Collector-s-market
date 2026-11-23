<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\CouponManagementService;
use App\Services\ProductPresentationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Feature test cho chức năng B5: Quản lý khuyến mãi theo sản phẩm và mã giảm giá.
 */
class PromotionAndCouponAdminTest extends TestCase
{
    private User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        Gate::define('coupons.manage', static fn (User $user): bool => $user->hasPermission('coupons.manage'));

        $email = 'admin_b5_' . uniqid() . '@example.com';
        $adminId = DB::table('users')->insertGetId([
            'HoTen' => 'Quản Trị Khuyến Mãi',
            'Email' => $email,
            'MatKhau' => Hash::make('AdminPass123'),
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);

        $adminRole = DB::table('roles')->where('TenVaiTro', 'administrator')->first();
        if ($adminRole !== null) {
            DB::table('user_roles')->insertOrIgnore([
                'MaNguoiDung' => $adminId,
                'MaVaiTro' => $adminRole->MaVaiTro,
            ]);
        }

        $this->adminUser = User::query()->findOrFail($adminId);
    }

    public function test_admin_co_the_tao_sua_va_kiem_tra_ma_giam_gia(): void
    {
        $code = 'GIAM15_' . strtoupper(substr(uniqid(), -4));
        $start = Carbon::now()->subHour()->format('Y-m-d H:i:s');
        $end = Carbon::now()->addDays(7)->format('Y-m-d H:i:s');

        $response = $this->actingAs($this->adminUser)->post(route('admin.coupons.store'), [
            'MaUuDai' => strtolower($code),
            'LoaiGiamGia' => 'percent',
            'GiaTriGiam' => 15,
            'DonHangToiThieu' => 200000,
            'SoLanSuDungToiDa' => 50,
            'SoLanSuDungToiDaMoiNguoi' => 2,
            'BatDauLuc' => $start,
            'KetThucLuc' => $end,
            'TrangThai' => 'active',
        ]);

        $response->assertRedirect(route('admin.coupons.index'));

        $coupon = DB::table('coupons')->where('MaUuDai', $code)->first();
        $this->assertNotNull($coupon);
        $this->assertSame(15.0, (float) $coupon->GiaTriGiam);

        /** @var CouponManagementService $couponService */
        $couponService = app(CouponManagementService::class);

        // Chưa đạt đơn hàng tối thiểu 200.000đ -> từ chối
        $belowMin = $couponService->validateAndCalculateDiscount($code, 150000);
        $this->assertFalse($belowMin['valid']);

        // Đạt đơn tối thiểu -> giảm 15% của 400.000đ = 60.000đ
        $validResult = $couponService->validateAndCalculateDiscount($code, 400000);
        $this->assertTrue($validResult['valid']);
        $this->assertSame(60000.0, $validResult['discount_amount']);
    }

    public function test_tu_choi_ma_giam_gia_phan_tram_vuot_qua_100_hoac_ngay_ket_thuc_truoc_ngay_bat_dau(): void
    {
        $response = $this->actingAs($this->adminUser)
            ->from(route('admin.coupons.create'))
            ->post(route('admin.coupons.store'), [
                'MaUuDai' => 'LOI_PHAN_TRAM',
                'LoaiGiamGia' => 'percent',
                'GiaTriGiam' => 120,
                'BatDauLuc' => '2026-11-20 10:00:00',
                'KetThucLuc' => '2026-11-15 10:00:00',
                'TrangThai' => 'active',
            ]);

        $response->assertRedirect(route('admin.coupons.create'));
        $response->assertSessionHasErrors(['GiaTriGiam', 'KetThucLuc']);
    }

    public function test_xoa_ma_giam_gia_da_su_dung_se_chuyen_sang_ngung_kich_hoat(): void
    {
        $code = 'USED_' . strtoupper(substr(uniqid(), -5));
        $couponId = DB::table('coupons')->insertGetId([
            'MaUuDai' => $code,
            'LoaiGiamGia' => 'fixed',
            'GiaTriGiam' => 50000,
            'DonHangToiThieu' => 0,
            'SoLanDaSuDung' => 1,
            'BatDauLuc' => Carbon::now()->subDay(),
            'KetThucLuc' => Carbon::now()->addDay(),
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->adminUser)
            ->delete(route('admin.coupons.destroy', $couponId));

        $response->assertRedirect(route('admin.coupons.index'));

        $couponAfter = DB::table('coupons')->where('MaPhieuGiamGia', $couponId)->first();
        $this->assertNotNull($couponAfter);
        $this->assertSame('inactive', $couponAfter->TrangThai);
    }

    public function test_admin_co_the_tao_khuyen_mai_theo_san_pham_va_tinh_gia_sau_giam(): void
    {
        $category = DB::table('categories')->first();
        $categoryId = $category !== null
            ? (int) $category->MaDanhMuc
            : (int) DB::table('categories')->insertGetId([
                'TenDanhMuc' => 'Mô hình Test',
                'DuongDan' => 'mo-hinh-test-' . uniqid(),
                'TrangThai' => 'active',
                'NgayTao' => Carbon::now(),
                'NgayCapNhat' => Carbon::now(),
            ]);

        $productId = (int) DB::table('products')->insertGetId([
            'MaDanhMuc' => $categoryId,
            'MaSKU' => 'SKU-B5-' . strtoupper(substr(uniqid(), -5)),
            'TenSanPham' => 'Mô hình Gundam B5 Test',
            'DuongDan' => 'mo-hinh-gundam-b5-' . uniqid(),
            'Gia' => 500000,
            'SoLuongTon' => 10,
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);

        $response = $this->actingAs($this->adminUser)->post(route('admin.promotions.store'), [
            'TenKhuyenMai' => 'Giảm 20% Mô hình Gundam',
            'MoTa' => 'Khuyến mãi theo sản phẩm',
            'LoaiGiamGia' => 'percent',
            'GiaTriGiam' => 20,
            'BatDauLuc' => Carbon::now()->subHour()->format('Y-m-d H:i:s'),
            'KetThucLuc' => Carbon::now()->addDays(3)->format('Y-m-d H:i:s'),
            'TrangThai' => 'active',
            'san_pham_ids' => [$productId],
        ]);

        $response->assertRedirect(route('admin.promotions.index'));

        /** @var ProductPresentationService $presentation */
        $presentation = app(ProductPresentationService::class);
        $rawProduct = $presentation->basePublicProductQuery()
            ->where('products.MaSanPham', $productId)
            ->get();

        $enriched = $presentation->enrichProducts($rawProduct)->first();
        $this->assertNotNull($enriched);
        $this->assertTrue($enriched->CoGiamGia);
        $this->assertSame(400000.0, (float) $enriched->GiaSauGiam);
    }
}
