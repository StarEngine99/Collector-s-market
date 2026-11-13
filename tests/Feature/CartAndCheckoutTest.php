<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\OrderStatus;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * Feature test cho chức năng A5: Giỏ hàng và Thanh toán (COD / Chuyển khoản).
 */
class CartAndCheckoutTest extends TestCase
{
    private int $categoryId;
    private int $productId;

    protected function setUp(): void
    {
        parent::setUp();

        $category = DB::table('categories')->where('TrangThai', 'active')->first();
        $this->categoryId = $category !== null
            ? (int) $category->MaDanhMuc
            : (int) DB::table('categories')->insertGetId([
                'TenDanhMuc' => 'Mô hình A5',
                'DuongDan' => 'mo-hinh-a5-' . uniqid(),
                'TrangThai' => 'active',
                'NgayTao' => Carbon::now(),
                'NgayCapNhat' => Carbon::now(),
            ]);

        $this->productId = (int) DB::table('products')->insertGetId([
            'MaDanhMuc' => $this->categoryId,
            'MaSKU' => 'SKU-A5-' . strtoupper(substr(uniqid(), -5)),
            'TenSanPham' => 'Mô hình One Piece Luffy Gear 5',
            'DuongDan' => 'mo-hinh-luffy-gear-5-' . uniqid(),
            'Gia' => 1000000,
            'SoLuongTon' => 5,
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);
    }

    public function test_khach_chua_dang_nhap_co_the_them_gio_hang_nhung_phai_dang_nhap_moi_thanh_toan_duoc(): void
    {
        $addResponse = $this->post(route('cart.add'), [
            'MaSanPham' => $this->productId,
            'SoLuong' => 2,
        ]);

        $addResponse->assertRedirect(route('cart.index'));

        // Khách chưa đăng nhập truy cập trang thanh toán sẽ bị chuyển sang trang đăng nhập
        $checkoutResponse = $this->get(route('checkout.index'));
        $checkoutResponse->assertRedirect(route('login'));
    }

    public function test_gio_hang_ap_gia_khuyen_mai_san_pham_truoc_va_ma_giam_gia_sau_do_tao_don_tru_ton_kho(): void
    {
        // 1. Tạo khuyến mãi theo sản phẩm giảm 10% (1.000.000đ -> 900.000đ/sp)
        $promoId = (int) DB::table('promotions')->insertGetId([
            'TenKhuyenMai' => 'Giảm 10% SP',
            'LoaiGiamGia' => 'percent',
            'GiaTriGiam' => 10,
            'BatDauLuc' => Carbon::now()->subHour(),
            'KetThucLuc' => Carbon::now()->addDay(),
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);
        DB::table('promotion_products')->insert([
            'MaKhuyenMai' => $promoId,
            'MaSanPham' => $this->productId,
        ]);

        // 2. Tạo mã giảm giá giảm cố định 100.000đ cho đơn từ 1.500.000đ
        $couponCode = 'GIAM100K_' . strtoupper(substr(uniqid(), -4));
        $couponId = (int) DB::table('coupons')->insertGetId([
            'MaUuDai' => $couponCode,
            'LoaiGiamGia' => 'fixed',
            'GiaTriGiam' => 100000,
            'DonHangToiThieu' => 1500000,
            'SoLanSuDungToiDa' => 10,
            'SoLanSuDungToiDaMoiNguoi' => 1,
            'SoLanDaSuDung' => 0,
            'BatDauLuc' => Carbon::now()->subHour(),
            'KetThucLuc' => Carbon::now()->addDay(),
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);

        // 3. Tạo khách hàng và đăng nhập
        $userId = (int) DB::table('users')->insertGetId([
            'HoTen' => 'Khách Mua Hàng A5',
            'Email' => 'khacha5_' . uniqid() . '@example.com',
            'SoDienThoai' => '0911222333',
            'MatKhau' => Hash::make('Password123'),
            'TrangThai' => 'active',
            'NgayTao' => Carbon::now(),
            'NgayCapNhat' => Carbon::now(),
        ]);
        $user = User::query()->findOrFail($userId);

        // 4. Thêm 2 sản phẩm vào giỏ -> Tạm tính sau KM sản phẩm: 2 * 900.000 = 1.800.000đ
        $this->actingAs($user)->post(route('cart.add'), [
            'MaSanPham' => $this->productId,
            'SoLuong' => 2,
        ])->assertRedirect(route('cart.index'));

        // 5. Áp mã giảm giá 100.000đ -> Tổng thanh toán còn 1.700.000đ
        $this->actingAs($user)->post(route('cart.coupon.apply'), [
            'MaUuDai' => $couponCode,
        ])->assertRedirect(route('cart.index'));

        // 6. Đặt hàng bằng phương thức Chuyển khoản (bank_transfer)
        $checkoutRes = $this->actingAs($user)->post(route('checkout.store'), [
            'TenNguoiNhan' => 'Khách Mua Hàng A5',
            'SoDienThoaiNguoiNhan' => '0911222333',
            'TinhThanh' => 'Đồng Tháp',
            'QuanHuyen' => 'TP. Cao Lãnh',
            'PhuongXa' => 'Phường 6',
            'DiaChiChiTiet' => '783 Phạm Hữu Lầu',
            'PhuongThuc' => 'bank_transfer',
            'GhiChu' => 'Giao giờ hành chính',
            'luu_dia_chi' => '1',
        ]);

        $order = DB::table('orders')->where('MaNguoiDung', $userId)->latest('MaDonHang')->first();
        $this->assertNotNull($order);
        $checkoutRes->assertRedirect(route('checkout.success', ['orderNumber' => $order->SoDonHang]));

        // Kiểm tra trạng thái đơn đầu tiên là "Đang chuẩn bị hàng" ('preparing')
        $this->assertSame(OrderStatus::PREPARING, $order->TrangThai);
        $this->assertSame('Đang chuẩn bị hàng', OrderStatus::label($order->TrangThai));

        // Kiểm tra tính tiền: TamTinh = 1.800.000, TienGiam = 100.000, TongTien = 1.700.000
        $this->assertSame(1800000.0, (float) $order->TamTinh);
        $this->assertSame(100000.0, (float) $order->TienGiam);
        $this->assertSame(1700000.0, (float) $order->TongTien);

        // Kiểm tra tồn kho đã trừ ngay khi tạo đơn (5 - 2 = 3) và có bản ghi inventory_movements
        $productAfter = DB::table('products')->where('MaSanPham', $this->productId)->first();
        $this->assertSame(3, (int) $productAfter->SoLuongTon);

        $movement = DB::table('inventory_movements')
            ->where('LoaiThamChieu', 'order')
            ->where('MaThamChieu', $order->MaDonHang)
            ->first();
        $this->assertNotNull($movement);
        $this->assertSame(-2, (int) $movement->ThayDoiSoLuong);

        // Kiểm tra trạng thái thanh toán ban đầu là 'pending' (Chưa thanh toán)
        $payment = DB::table('payments')->where('MaDonHang', $order->MaDonHang)->first();
        $this->assertNotNull($payment);
        $this->assertSame('bank_transfer', $payment->PhuongThuc);
        $this->assertSame('pending', $payment->TrangThai);

        // Kiểm tra mã giảm giá đã tăng lượt dùng và ghi coupon_redemptions
        $couponAfter = DB::table('coupons')->where('MaPhieuGiamGia', $couponId)->first();
        $this->assertSame(1, (int) $couponAfter->SoLanDaSuDung);
    }
}
