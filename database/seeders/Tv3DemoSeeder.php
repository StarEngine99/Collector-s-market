<?php

namespace Database\Seeders;

use App\Support\OrderStatus;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder dữ liệu mẫu cho phạm vi TV3 (D4):
 * - Tài khoản mẫu (Quản trị viên, Nhân viên đơn hàng, Khách hàng hoạt động, Khách hàng bị khóa)
 * - Khuyến mãi theo sản phẩm & Mã giảm giá mẫu (B5)
 * - Địa chỉ giao hàng & Đơn hàng mẫu ở các trạng thái khác nhau (A5, B3, A7, B4)
 */
class Tv3DemoSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $this->seedRolesAndPermissions();
            $users = $this->seedUsers();
            $products = $this->seedSampleCatalogIfNeeded();
            $coupons = $this->seedPromotionsAndCoupons($products);
            $this->seedAddressesAndOrders($users, $products, $coupons);
        });
    }

    private function seedRolesAndPermissions(): void
    {
        $roles = [
            ['TenVaiTro' => 'administrator', 'MoTa' => 'Quản trị hệ thống'],
            ['TenVaiTro' => 'catalog_manager', 'MoTa' => 'Phụ trách sản phẩm, danh mục và tồn kho'],
            ['TenVaiTro' => 'order_staff', 'MoTa' => 'Phụ trách đơn hàng và khách hàng'],
            ['TenVaiTro' => 'content_staff', 'MoTa' => 'Phụ trách bài viết và banner'],
            ['TenVaiTro' => 'analyst', 'MoTa' => 'Phụ trách thống kê và báo cáo'],
            ['TenVaiTro' => 'customer', 'MoTa' => 'Khách hàng mua sắm trên hệ thống'],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['TenVaiTro' => $role['TenVaiTro']],
                ['MoTa' => $role['MoTa']]
            );
        }
    }

    /**
     * @return array<string, int>
     */
    private function seedUsers(): array
    {
        $accounts = [
            'admin' => [
                'HoTen' => 'Quản Trị Viên Hệ Thống',
                'Email' => 'admin@collectorsmarket.local',
                'SoDienThoai' => '0901000001',
                'MatKhau' => Hash::make('Admin@123456'),
                'TrangThai' => 'active',
                'role' => 'administrator',
            ],
            'order_staff' => [
                'HoTen' => 'Nhân Viên Đơn Hàng',
                'Email' => 'orderstaff@collectorsmarket.local',
                'SoDienThoai' => '0901000002',
                'MatKhau' => Hash::make('Staff@123456'),
                'TrangThai' => 'active',
                'role' => 'order_staff',
            ],
            'customer1' => [
                'HoTen' => 'Nguyễn Minh Hoàng',
                'Email' => 'khachhang1@collectorsmarket.local',
                'SoDienThoai' => '0909111222',
                'MatKhau' => Hash::make('Customer@123456'),
                'TrangThai' => 'active',
                'role' => 'customer',
            ],
            'customer2' => [
                'HoTen' => 'Trần Thảo Vy',
                'Email' => 'khachhang2@collectorsmarket.local',
                'SoDienThoai' => '0909333444',
                'MatKhau' => Hash::make('Customer@123456'),
                'TrangThai' => 'active',
                'role' => 'customer',
            ],
            'locked_customer' => [
                'HoTen' => 'Lê Văn Khóa (Tài khoản test khóa)',
                'Email' => 'khachkhoa@collectorsmarket.local',
                'SoDienThoai' => '0909555666',
                'MatKhau' => Hash::make('Customer@123456'),
                'TrangThai' => 'locked',
                'role' => 'customer',
            ],
        ];

        $userIds = [];

        foreach ($accounts as $key => $acc) {
            DB::table('users')->updateOrInsert(
                ['Email' => $acc['Email']],
                [
                    'HoTen' => $acc['HoTen'],
                    'SoDienThoai' => $acc['SoDienThoai'],
                    'MatKhau' => $acc['MatKhau'],
                    'TrangThai' => $acc['TrangThai'],
                    'EmailXacThucLuc' => now(),
                ]
            );

            $userId = (int) DB::table('users')->where('Email', $acc['Email'])->value('MaNguoiDung');
            $roleId = (int) DB::table('roles')->where('TenVaiTro', $acc['role'])->value('MaVaiTro');

            if ($userId > 0 && $roleId > 0) {
                DB::table('user_roles')->updateOrInsert([
                    'MaNguoiDung' => $userId,
                    'MaVaiTro' => $roleId,
                ]);
            }

            $userIds[$key] = $userId;
        }

        return $userIds;
    }

    /**
     * Tạo danh mục & sản phẩm mẫu nếu DB chưa có sẵn sản phẩm để liên kết đơn hàng & khuyến mãi mẫu.
     *
     * @return array<int, object>
     */
    private function seedSampleCatalogIfNeeded(): array
    {
        DB::table('categories')->updateOrInsert(
            ['DuongDan' => 'mo-hinh-gundam-gunpla'],
            [
                'TenDanhMuc' => 'Mô hình Gundam / Gunpla',
                'MoTa' => 'Mô hình lắp ráp Bandai chính hãng các cấp độ HG, RG, MG.',
                'ThuTuHienThi' => 1,
                'DangHoatDong' => 1,
            ]
        );

        DB::table('categories')->updateOrInsert(
            ['DuongDan' => 'the-bai-suu-tam-tcg'],
            [
                'TenDanhMuc' => 'Thẻ bài sưu tầm TCG',
                'MoTa' => 'Thẻ bài Pokémon TCG, Yu-Gi-Oh!, One Piece Card Game chính hãng.',
                'ThuTuHienThi' => 2,
                'DangHoatDong' => 1,
            ]
        );

        $gundamCatId = (int) DB::table('categories')->where('DuongDan', 'mo-hinh-gundam-gunpla')->value('MaDanhMuc');
        $tcgCatId = (int) DB::table('categories')->where('DuongDan', 'the-bai-suu-tam-tcg')->value('MaDanhMuc');

        $sampleProducts = [
            [
                'MaDanhMuc' => $gundamCatId,
                'TenSanPham' => 'Mô hình RG 1/144 Hi-Nu Gundam Bandai',
                'DuongDan' => 'mo-hinh-rg-1-144-hi-nu-gundam-bandai',
                'SKU' => 'GUN-RG-HINU-01',
                'ThuongHieu' => 'Bandai Namco',
                'DongSanPham' => 'Real Grade (RG)',
                'TyLeMoHinh' => '1/144',
                'GiaGoc' => 1350000,
                'GiaBan' => 1200000,
                'SoLuongTon' => 25,
                'SoLuongCanhBao' => 5,
                'NoiBat' => 1,
                'TrangThai' => 'active',
                'MoTaNgan' => 'Mô hình lắp ráp RG Hi-Nu Gundam chi tiết cao kèm hiệu ứng Fin Funnel.',
            ],
            [
                'MaDanhMuc' => $gundamCatId,
                'TenSanPham' => 'Mô hình HG 1/144 Gundam Aerial Rebuild',
                'DuongDan' => 'mo-hinh-hg-1-144-gundam-aerial-rebuild',
                'SKU' => 'GUN-HG-AERIAL-02',
                'ThuongHieu' => 'Bandai Namco',
                'DongSanPham' => 'High Grade (HG)',
                'TyLeMoHinh' => '1/144',
                'GiaGoc' => 520000,
                'GiaBan' => 450000,
                'SoLuongTon' => 40,
                'SoLuongCanhBao' => 5,
                'NoiBat' => 1,
                'TrangThai' => 'active',
                'MoTaNgan' => 'Phiên bản nâng cấp Gundam Aerial Rebuild từ series The Witch from Mercury.',
            ],
            [
                'MaDanhMuc' => $tcgCatId,
                'TenSanPham' => 'Hộp Booster Box Pokémon TCG Scarlet & Violet 151',
                'DuongDan' => 'hop-booster-box-pokemon-tcg-scarlet-violet-151',
                'SKU' => 'TCG-PKM-151-BOX',
                'ThuongHieu' => 'The Pokémon Company',
                'DongSanPham' => 'Scarlet & Violet',
                'DoHiemThe' => 'Booster Box (20 Packs)',
                'GiaGoc' => 1950000,
                'GiaBan' => 1800000,
                'SoLuongTon' => 15,
                'SoLuongCanhBao' => 3,
                'NoiBat' => 1,
                'TrangThai' => 'active',
                'MoTaNgan' => 'Hộp thẻ bài Pokémon 151 nguyên seal dành cho nhà sưu tầm.',
            ],
        ];

        $products = [];
        foreach ($sampleProducts as $prod) {
            DB::table('products')->updateOrInsert(
                ['SKU' => $prod['SKU']],
                $prod
            );
            $products[] = DB::table('products')->where('SKU', $prod['SKU'])->first();
        }

        return $products;
    }

    /**
     * @param  array<int, object>  $products
     * @return array<string, int>
     */
    private function seedPromotionsAndCoupons(array $products): array
    {
        // 1. Khuyến mãi theo sản phẩm (B5)
        DB::table('promotions')->updateOrInsert(
            ['TenChuongTrinh' => 'Ưu đãi Gunpla Cuối Tuần (-10%)'],
            [
                'MoTa' => 'Giảm trực tiếp 10% cho các mẫu Gunpla tiêu biểu.',
                'KieuGiam' => 'percentage',
                'GiaTriGiam' => 10,
                'BatDauLuc' => now()->subDays(5),
                'KetThucLuc' => now()->addDays(30),
                'DangHoatDong' => 1,
            ]
        );

        $promoId = (int) DB::table('promotions')
            ->where('TenChuongTrinh', 'Ưu đãi Gunpla Cuối Tuần (-10%)')
            ->value('MaKhuyenMai');

        if ($promoId > 0 && isset($products[0])) {
            DB::table('promotion_products')->updateOrInsert([
                'MaKhuyenMai' => $promoId,
                'MaSanPham' => $products[0]->MaSanPham,
            ]);
        }

        // 2. Mã giảm giá (Coupons - B5)
        $couponRows = [
            'COLLECTOR10' => [
                'MaCode' => 'COLLECTOR10',
                'TenMa' => 'Giảm 10% đơn từ 300K (Tối đa 100K)',
                'KieuGiam' => 'percentage',
                'GiaTriGiam' => 10,
                'GiaTriDonToiThieu' => 300000,
                'GiamToiDa' => 100000,
                'GioiHanSuDung' => 100,
                'SoLanDaDung' => 1,
                'BatDauLuc' => now()->subDays(10),
                'KetThucLuc' => now()->addDays(60),
                'DangHoatDong' => 1,
            ],
            'GIAM50K' => [
                'MaCode' => 'GIAM50K',
                'TenMa' => 'Giảm 50.000đ cho đơn từ 500.000đ',
                'KieuGiam' => 'fixed',
                'GiaTriGiam' => 50000,
                'GiaTriDonToiThieu' => 500000,
                'GiamToiDa' => null,
                'GioiHanSuDung' => 50,
                'SoLanDaDung' => 0,
                'BatDauLuc' => now()->subDays(5),
                'KetThucLuc' => now()->addDays(45),
                'DangHoatDong' => 1,
            ],
            'EXPIRED20' => [
                'MaCode' => 'EXPIRED20',
                'TenMa' => 'Mã giảm giá đã hết hạn (dùng để kiểm thử)',
                'KieuGiam' => 'percentage',
                'GiaTriGiam' => 20,
                'GiaTriDonToiThieu' => 200000,
                'GiamToiDa' => 150000,
                'GioiHanSuDung' => 20,
                'SoLanDaDung' => 0,
                'BatDauLuc' => now()->subDays(40),
                'KetThucLuc' => now()->subDays(5),
                'DangHoatDong' => 1,
            ],
        ];

        $couponIds = [];
        foreach ($couponRows as $code => $row) {
            DB::table('coupons')->updateOrInsert(['MaCode' => $code], $row);
            $couponIds[$code] = (int) DB::table('coupons')->where('MaCode', $code)->value('MaPhieuGiamGia');
        }

        return $couponIds;
    }

    /**
     * @param  array<string, int>  $users
     * @param  array<int, object>  $products
     * @param  array<string, int>  $coupons
     */
    private function seedAddressesAndOrders(array $users, array $products, array $coupons): void
    {
        if (empty($products) || empty($users['customer1']) || empty($users['customer2'])) {
            return;
        }

        $customer1Id = $users['customer1'];
        $customer2Id = $users['customer2'];

        // Địa chỉ mẫu cho khách hàng 1
        DB::table('addresses')->updateOrInsert(
            [
                'MaNguoiDung' => $customer1Id,
                'TenNguoiNhan' => 'Nguyễn Minh Hoàng',
            ],
            [
                'SoDienThoai' => '0909111222',
                'TinhThanh' => 'Đồng Tháp',
                'QuanHuyen' => 'TP. Cao Lãnh',
                'PhuongXa' => 'Phường 6',
                'DiaChiChiTiet' => '783 Phạm Hữu Lầu (Khu vực ĐH Đồng Tháp)',
                'LaMacDinh' => 1,
            ]
        );

        // Đơn hàng mẫu 1: Đang chuẩn bị hàng (preparing) - Khách có thể hủy trong A7
        $this->upsertSampleOrder([
            'SoDonHang' => 'CM202611130001',
            'MaNguoiDung' => $customer1Id,
            'TrangThai' => OrderStatus::PREPARING,
            'TamTinh' => 900000,
            'SoTienGiamGia' => 90000,
            'PhiVanChuyen' => 0,
            'TongThanhToan' => 810000,
            'MaPhieuGiamGia' => $coupons['COLLECTOR10'] ?? null,
            'TenNguoiNhan' => 'Nguyễn Minh Hoàng',
            'SoDienThoaiNhan' => '0909111222',
            'DiaChiGiaoHang' => '783 Phạm Hữu Lầu, Phường 6, TP. Cao Lãnh, Đồng Tháp',
            'GhiChu' => 'Giao giờ hành chính, đóng gói chống sốc hộp mô hình.',
        ], [
            [
                'product' => $products[1],
                'DonGia' => 450000,
                'SoLuong' => 2,
                'ThanhTien' => 900000,
            ],
        ], OrderStatus::PAYMENT_METHOD_COD, OrderStatus::PAYMENT_STATUS_PENDING);

        // Đơn hàng mẫu 2: Đang giao hàng (shipping) - Chuyển khoản ngân hàng - Khách không thể tự hủy
        $this->upsertSampleOrder([
            'SoDonHang' => 'CM202611130002',
            'MaNguoiDung' => $customer1Id,
            'TrangThai' => OrderStatus::SHIPPING,
            'TamTinh' => 1800000,
            'SoTienGiamGia' => 0,
            'PhiVanChuyen' => 0,
            'TongThanhToan' => 1800000,
            'MaPhieuGiamGia' => null,
            'TenNguoiNhan' => 'Nguyễn Minh Hoàng',
            'SoDienThoaiNhan' => '0909111222',
            'DiaChiGiaoHang' => '783 Phạm Hữu Lầu, Phường 6, TP. Cao Lãnh, Đồng Tháp',
            'GhiChu' => 'Đơn chuyển khoản Booster Box Pokémon 151.',
        ], [
            [
                'product' => $products[2],
                'DonGia' => 1800000,
                'SoLuong' => 1,
                'ThanhTien' => 1800000,
            ],
        ], OrderStatus::PAYMENT_METHOD_BANK_TRANSFER, OrderStatus::PAYMENT_STATUS_PAID);

        // Đơn hàng mẫu 3: Hoàn tất (completed) của khách hàng 2
        $this->upsertSampleOrder([
            'SoDonHang' => 'CM202611130003',
            'MaNguoiDung' => $customer2Id,
            'TrangThai' => OrderStatus::COMPLETED,
            'TamTinh' => 1080000,
            'SoTienGiamGia' => 0,
            'PhiVanChuyen' => 0,
            'TongThanhToan' => 1080000,
            'MaPhieuGiamGia' => null,
            'TenNguoiNhan' => 'Trần Thảo Vy',
            'SoDienThoaiNhan' => '0909333444',
            'DiaChiGiaoHang' => '45 Nguyễn Huệ, Phường 1, TP. Sa Đéc, Đồng Tháp',
            'GhiChu' => 'Đã giao thành công.',
        ], [
            [
                'product' => $products[0],
                'DonGia' => 1080000,
                'SoLuong' => 1,
                'ThanhTien' => 1080000,
            ],
        ], OrderStatus::PAYMENT_METHOD_COD, OrderStatus::PAYMENT_STATUS_PAID);
    }

    /**
     * @param  array<string, mixed>  $orderData
     * @param  array<int, array{product: object, DonGia: float|int, SoLuong: int, ThanhTien: float|int}>  $items
     */
    private function upsertSampleOrder(
        array $orderData,
        array $items,
        string $paymentMethod,
        string $paymentStatus
    ): void {
        $existingOrderId = DB::table('orders')
            ->where('SoDonHang', $orderData['SoDonHang'])
            ->value('MaDonHang');

        if ($existingOrderId) {
            return;
        }

        $orderId = (int) DB::table('orders')->insertGetId($orderData);

        foreach ($items as $item) {
            $prod = $item['product'];
            DB::table('order_items')->insert([
                'MaDonHang' => $orderId,
                'MaSanPham' => $prod->MaSanPham,
                'TenSanPham' => $prod->TenSanPham,
                'SKU' => $prod->SKU,
                'DonGia' => $item['DonGia'],
                'SoLuong' => $item['SoLuong'],
                'ThanhTien' => $item['ThanhTien'],
            ]);
        }

        DB::table('payments')->insert([
            'MaDonHang' => $orderId,
            'PhuongThuc' => $paymentMethod,
            'TrangThai' => $paymentStatus,
            'SoTien' => $orderData['TongThanhToan'],
            'MaGiaoDich' => $paymentStatus === OrderStatus::PAYMENT_STATUS_PAID ? 'DEMO-TXN-' . $orderData['SoDonHang'] : null,
            'ThanhToanLuc' => $paymentStatus === OrderStatus::PAYMENT_STATUS_PAID ? now() : null,
        ]);

        if (! empty($orderData['MaPhieuGiamGia'])) {
            DB::table('coupon_redemptions')->insert([
                'MaPhieuGiamGia' => $orderData['MaPhieuGiamGia'],
                'MaNguoiDung' => $orderData['MaNguoiDung'],
                'MaDonHang' => $orderId,
                'SoTienGiam' => $orderData['SoTienGiamGia'],
            ]);
        }
    }
}
