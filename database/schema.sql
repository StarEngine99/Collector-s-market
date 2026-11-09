-- MySQL 8.0+ schema for a model and trading-card store.
-- Import with: mysql -u root -p < database/schema.sql

CREATE DATABASE IF NOT EXISTS ChoFigureThe
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE ChoFigureThe;

CREATE TABLE users (
    MaNguoiDung BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    HoTen VARCHAR(150) NOT NULL,
    Email VARCHAR(190) NOT NULL,
    SoDienThoai VARCHAR(30) NULL,
    MatKhau VARCHAR(255) NOT NULL,
    TrangThai VARCHAR(20) NOT NULL DEFAULT 'active',
    XacThucEmailLuc TIMESTAMP NULL,
    MaGhiNho VARCHAR(100) NULL,
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    NgayXoa TIMESTAMP NULL,
    PRIMARY KEY (MaNguoiDung),
    UNIQUE KEY users_email_unique (Email),
    KEY users_status_index (TrangThai),
    CONSTRAINT users_status_check CHECK (TrangThai IN ('active', 'locked'))
) ENGINE=InnoDB;

CREATE TABLE roles (
    MaVaiTro BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    TenVaiTro VARCHAR(80) NOT NULL,
    TenHienThi VARCHAR(120) NOT NULL,
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (MaVaiTro),
    UNIQUE KEY roles_name_unique (TenVaiTro)
) ENGINE=InnoDB;

CREATE TABLE permissions (
    MaQuyen BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    TenQuyen VARCHAR(100) NOT NULL,
    TenHienThi VARCHAR(150) NOT NULL,
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (MaQuyen),
    UNIQUE KEY permissions_name_unique (TenQuyen)
) ENGINE=InnoDB;

CREATE TABLE user_roles (
    MaNguoiDung BIGINT UNSIGNED NOT NULL,
    MaVaiTro BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (MaNguoiDung, MaVaiTro),
    CONSTRAINT user_roles_user_fk FOREIGN KEY (MaNguoiDung) REFERENCES users (MaNguoiDung) ON DELETE CASCADE,
    CONSTRAINT user_roles_role_fk FOREIGN KEY (MaVaiTro) REFERENCES roles (MaVaiTro) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE role_permissions (
    MaVaiTro BIGINT UNSIGNED NOT NULL,
    MaQuyen BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (MaVaiTro, MaQuyen),
    CONSTRAINT role_permissions_role_fk FOREIGN KEY (MaVaiTro) REFERENCES roles (MaVaiTro) ON DELETE CASCADE,
    CONSTRAINT role_permissions_permission_fk FOREIGN KEY (MaQuyen) REFERENCES permissions (MaQuyen) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE addresses (
    MaDiaChi BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaNguoiDung BIGINT UNSIGNED NOT NULL,
    TenNguoiNhan VARCHAR(150) NOT NULL,
    SoDienThoai VARCHAR(30) NOT NULL,
    TinhThanh VARCHAR(120) NOT NULL,
    QuanHuyen VARCHAR(120) NOT NULL,
    PhuongXa VARCHAR(120) NOT NULL,
    DiaChiChiTiet VARCHAR(255) NOT NULL,
    MacDinh BOOLEAN NOT NULL DEFAULT FALSE,
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (MaDiaChi),
    KEY addresses_user_index (MaNguoiDung),
    CONSTRAINT addresses_user_fk FOREIGN KEY (MaNguoiDung) REFERENCES users (MaNguoiDung) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE categories (
    MaDanhMuc BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaDanhMucCha BIGINT UNSIGNED NULL,
    TenDanhMuc VARCHAR(150) NOT NULL,
    DuongDan VARCHAR(180) NOT NULL,
    MoTa TEXT NULL,
    ThuTuSapXep INT UNSIGNED NOT NULL DEFAULT 0,
    TrangThai VARCHAR(20) NOT NULL DEFAULT 'active',
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    NgayXoa TIMESTAMP NULL,
    PRIMARY KEY (MaDanhMuc),
    UNIQUE KEY categories_slug_unique (DuongDan),
    KEY categories_parent_status_index (MaDanhMucCha, TrangThai),
    CONSTRAINT categories_parent_fk FOREIGN KEY (MaDanhMucCha) REFERENCES categories (MaDanhMuc) ON DELETE SET NULL,
    CONSTRAINT categories_status_check CHECK (TrangThai IN ('active', 'hidden'))
) ENGINE=InnoDB;

CREATE TABLE products (
    MaSanPham BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaDanhMuc BIGINT UNSIGNED NOT NULL,
    MaSKU VARCHAR(80) NOT NULL,
    TenSanPham VARCHAR(200) NOT NULL,
    DuongDan VARCHAR(220) NOT NULL,
    MoTa TEXT NULL,
    Gia DECIMAL(12,2) NOT NULL,
    SoLuongTon INT UNSIGNED NOT NULL DEFAULT 0,
    TrangThai VARCHAR(20) NOT NULL DEFAULT 'draft',
    NoiBat BOOLEAN NOT NULL DEFAULT FALSE,
    NgayDang TIMESTAMP NULL,
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    NgayXoa TIMESTAMP NULL,
    PRIMARY KEY (MaSanPham),
    UNIQUE KEY products_sku_unique (MaSKU),
    UNIQUE KEY products_slug_unique (DuongDan),
    KEY products_category_status_index (MaDanhMuc, TrangThai),
    KEY products_price_index (Gia),
    KEY products_published_index (NgayDang),
    CONSTRAINT products_category_fk FOREIGN KEY (MaDanhMuc) REFERENCES categories (MaDanhMuc) ON DELETE RESTRICT,
    CONSTRAINT products_price_check CHECK (Gia >= 0),
    CONSTRAINT products_status_check CHECK (TrangThai IN ('draft', 'active', 'out_of_stock', 'hidden'))
) ENGINE=InnoDB;

CREATE TABLE product_images (
    MaHinhAnh BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaSanPham BIGINT UNSIGNED NOT NULL,
    DuongDanHinhAnh VARCHAR(500) NOT NULL,
    MoTaHinhAnh VARCHAR(255) NULL,
    ThuTuSapXep INT UNSIGNED NOT NULL DEFAULT 0,
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (MaHinhAnh),
    KEY product_images_product_index (MaSanPham, ThuTuSapXep),
    CONSTRAINT product_images_product_fk FOREIGN KEY (MaSanPham) REFERENCES products (MaSanPham) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE inventory_movements (
    MaBienDongKho BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaSanPham BIGINT UNSIGNED NOT NULL,
    MaNguoiThucHien BIGINT UNSIGNED NULL,
    ThayDoiSoLuong INT NOT NULL,
    LyDo VARCHAR(255) NOT NULL,
    LoaiThamChieu VARCHAR(50) NULL,
    MaThamChieu BIGINT UNSIGNED NULL,
    NgayTao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (MaBienDongKho),
    KEY inventory_movements_product_date_index (MaSanPham, NgayTao),
    CONSTRAINT inventory_movements_product_fk FOREIGN KEY (MaSanPham) REFERENCES products (MaSanPham) ON DELETE RESTRICT,
    CONSTRAINT inventory_movements_actor_fk FOREIGN KEY (MaNguoiThucHien) REFERENCES users (MaNguoiDung) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE carts (
    MaGioHang BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaNguoiDung BIGINT UNSIGNED NULL,
    KhoaPhien VARCHAR(120) NULL,
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (MaGioHang),
    UNIQUE KEY carts_session_key_unique (KhoaPhien),
    KEY carts_user_index (MaNguoiDung),
    CONSTRAINT carts_user_fk FOREIGN KEY (MaNguoiDung) REFERENCES users (MaNguoiDung) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE cart_items (
    MaChiTietGioHang BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaGioHang BIGINT UNSIGNED NOT NULL,
    MaSanPham BIGINT UNSIGNED NOT NULL,
    SoLuong INT UNSIGNED NOT NULL,
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (MaChiTietGioHang),
    UNIQUE KEY cart_items_cart_product_unique (MaGioHang, MaSanPham),
    CONSTRAINT cart_items_cart_fk FOREIGN KEY (MaGioHang) REFERENCES carts (MaGioHang) ON DELETE CASCADE,
    CONSTRAINT cart_items_product_fk FOREIGN KEY (MaSanPham) REFERENCES products (MaSanPham) ON DELETE RESTRICT,
    CONSTRAINT cart_items_quantity_check CHECK (SoLuong > 0)
) ENGINE=InnoDB;

CREATE TABLE promotions (
    MaKhuyenMai BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    TenKhuyenMai VARCHAR(180) NOT NULL,
    MoTa TEXT NULL,
    LoaiGiamGia VARCHAR(20) NOT NULL,
    GiaTriGiam DECIMAL(12,2) NOT NULL,
    BatDauLuc DATETIME NOT NULL,
    KetThucLuc DATETIME NOT NULL,
    TrangThai VARCHAR(20) NOT NULL DEFAULT 'scheduled',
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (MaKhuyenMai),
    KEY promotions_status_dates_index (TrangThai, BatDauLuc, KetThucLuc),
    CONSTRAINT promotions_type_check CHECK (LoaiGiamGia IN ('percent', 'fixed')),
    CONSTRAINT promotions_value_check CHECK (GiaTriGiam > 0),
    CONSTRAINT promotions_status_check CHECK (TrangThai IN ('scheduled', 'active', 'inactive'))
) ENGINE=InnoDB;

CREATE TABLE promotion_products (
    MaKhuyenMai BIGINT UNSIGNED NOT NULL,
    MaSanPham BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (MaKhuyenMai, MaSanPham),
    CONSTRAINT promotion_products_promotion_fk FOREIGN KEY (MaKhuyenMai) REFERENCES promotions (MaKhuyenMai) ON DELETE CASCADE,
    CONSTRAINT promotion_products_product_fk FOREIGN KEY (MaSanPham) REFERENCES products (MaSanPham) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE coupons (
    MaPhieuGiamGia BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaUuDai VARCHAR(80) NOT NULL,
    LoaiGiamGia VARCHAR(20) NOT NULL,
    GiaTriGiam DECIMAL(12,2) NOT NULL,
    DonHangToiThieu DECIMAL(12,2) NOT NULL DEFAULT 0,
    SoLanSuDungToiDa INT UNSIGNED NULL,
    SoLanSuDungToiDaMoiNguoi INT UNSIGNED NULL,
    SoLanDaSuDung INT UNSIGNED NOT NULL DEFAULT 0,
    BatDauLuc DATETIME NOT NULL,
    KetThucLuc DATETIME NOT NULL,
    TrangThai VARCHAR(20) NOT NULL DEFAULT 'active',
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (MaPhieuGiamGia),
    UNIQUE KEY coupons_code_unique (MaUuDai),
    KEY coupons_status_dates_index (TrangThai, BatDauLuc, KetThucLuc),
    CONSTRAINT coupons_type_check CHECK (LoaiGiamGia IN ('percent', 'fixed')),
    CONSTRAINT coupons_value_check CHECK (GiaTriGiam > 0),
    CONSTRAINT coupons_minimum_check CHECK (DonHangToiThieu >= 0),
    CONSTRAINT coupons_status_check CHECK (TrangThai IN ('active', 'inactive'))
) ENGINE=InnoDB;

CREATE TABLE orders (
    MaDonHang BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaNguoiDung BIGINT UNSIGNED NULL,
    MaDiaChi BIGINT UNSIGNED NULL,
    MaPhieuGiamGia BIGINT UNSIGNED NULL,
    SoDonHang VARCHAR(40) NOT NULL,
    TrangThai VARCHAR(30) NOT NULL DEFAULT 'pending',
    TenNguoiNhan VARCHAR(150) NOT NULL,
    SoDienThoaiNguoiNhan VARCHAR(30) NOT NULL,
    DiaChiGiaoHang TEXT NOT NULL,
    TamTinh DECIMAL(12,2) NOT NULL,
    TienGiam DECIMAL(12,2) NOT NULL DEFAULT 0,
    PhiVanChuyen DECIMAL(12,2) NOT NULL DEFAULT 0,
    TongTien DECIMAL(12,2) NOT NULL,
    GhiChu TEXT NULL,
    DatLuc TIMESTAMP NULL,
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (MaDonHang),
    UNIQUE KEY orders_order_number_unique (SoDonHang),
    KEY orders_user_date_index (MaNguoiDung, NgayTao),
    KEY orders_status_date_index (TrangThai, NgayTao),
    CONSTRAINT orders_user_fk FOREIGN KEY (MaNguoiDung) REFERENCES users (MaNguoiDung) ON DELETE SET NULL,
    CONSTRAINT orders_address_fk FOREIGN KEY (MaDiaChi) REFERENCES addresses (MaDiaChi) ON DELETE SET NULL,
    CONSTRAINT orders_coupon_fk FOREIGN KEY (MaPhieuGiamGia) REFERENCES coupons (MaPhieuGiamGia) ON DELETE SET NULL,
    CONSTRAINT orders_status_check CHECK (TrangThai IN ('pending', 'confirmed', 'processing', 'shipped', 'completed', 'cancelled')),
    CONSTRAINT orders_amounts_check CHECK (TamTinh >= 0 AND TienGiam >= 0 AND PhiVanChuyen >= 0 AND TongTien >= 0)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    MaChiTietDonHang BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaDonHang BIGINT UNSIGNED NOT NULL,
    MaSanPham BIGINT UNSIGNED NULL,
    TenSanPham VARCHAR(200) NOT NULL,
    MaSKU VARCHAR(80) NOT NULL,
    DonGia DECIMAL(12,2) NOT NULL,
    SoLuong INT UNSIGNED NOT NULL,
    ThanhTien DECIMAL(12,2) NOT NULL,
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (MaChiTietDonHang),
    KEY order_items_order_index (MaDonHang),
    KEY order_items_product_index (MaSanPham),
    CONSTRAINT order_items_order_fk FOREIGN KEY (MaDonHang) REFERENCES orders (MaDonHang) ON DELETE CASCADE,
    CONSTRAINT order_items_product_fk FOREIGN KEY (MaSanPham) REFERENCES products (MaSanPham) ON DELETE SET NULL,
    CONSTRAINT order_items_values_check CHECK (DonGia >= 0 AND SoLuong > 0 AND ThanhTien >= 0)
) ENGINE=InnoDB;

CREATE TABLE coupon_redemptions (
    MaLuotSuDung BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaPhieuGiamGia BIGINT UNSIGNED NOT NULL,
    MaNguoiDung BIGINT UNSIGNED NULL,
    MaDonHang BIGINT UNSIGNED NOT NULL,
    TienGiam DECIMAL(12,2) NOT NULL,
    SuDungLuc TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (MaLuotSuDung),
    UNIQUE KEY coupon_redemptions_order_unique (MaDonHang),
    KEY coupon_redemptions_user_coupon_index (MaNguoiDung, MaPhieuGiamGia),
    CONSTRAINT coupon_redemptions_coupon_fk FOREIGN KEY (MaPhieuGiamGia) REFERENCES coupons (MaPhieuGiamGia) ON DELETE RESTRICT,
    CONSTRAINT coupon_redemptions_user_fk FOREIGN KEY (MaNguoiDung) REFERENCES users (MaNguoiDung) ON DELETE SET NULL,
    CONSTRAINT coupon_redemptions_order_fk FOREIGN KEY (MaDonHang) REFERENCES orders (MaDonHang) ON DELETE CASCADE,
    CONSTRAINT coupon_redemptions_amount_check CHECK (TienGiam >= 0)
) ENGINE=InnoDB;

CREATE TABLE payments (
    MaThanhToan BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaDonHang BIGINT UNSIGNED NOT NULL,
    PhuongThuc VARCHAR(30) NOT NULL,
    TrangThai VARCHAR(20) NOT NULL DEFAULT 'pending',
    MaGiaoDichNhaCungCap VARCHAR(150) NULL,
    SoTien DECIMAL(12,2) NOT NULL,
    ThanhToanLuc TIMESTAMP NULL,
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (MaThanhToan),
    KEY payments_order_index (MaDonHang),
    UNIQUE KEY payments_provider_reference_unique (MaGiaoDichNhaCungCap),
    CONSTRAINT payments_order_fk FOREIGN KEY (MaDonHang) REFERENCES orders (MaDonHang) ON DELETE CASCADE,
    CONSTRAINT payments_status_check CHECK (TrangThai IN ('pending', 'paid', 'failed', 'refunded')),
    CONSTRAINT payments_amount_check CHECK (SoTien >= 0)
) ENGINE=InnoDB;

CREATE TABLE posts (
    MaBaiViet BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaTacGia BIGINT UNSIGNED NULL,
    TieuDe VARCHAR(200) NOT NULL,
    DuongDan VARCHAR(220) NOT NULL,
    NoiDung LONGTEXT NOT NULL,
    TrangThai VARCHAR(20) NOT NULL DEFAULT 'draft',
    NgayDang TIMESTAMP NULL,
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (MaBaiViet),
    UNIQUE KEY posts_slug_unique (DuongDan),
    KEY posts_status_published_index (TrangThai, NgayDang),
    CONSTRAINT posts_author_fk FOREIGN KEY (MaTacGia) REFERENCES users (MaNguoiDung) ON DELETE SET NULL,
    CONSTRAINT posts_status_check CHECK (TrangThai IN ('draft', 'published', 'hidden'))
) ENGINE=InnoDB;

CREATE TABLE banners (
    MaBanner BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaNguoiQuanLy BIGINT UNSIGNED NULL,
    TieuDe VARCHAR(180) NOT NULL,
    DuongDanHinhAnh VARCHAR(500) NOT NULL,
    DuongDanDich VARCHAR(500) NULL,
    ThuTuSapXep INT UNSIGNED NOT NULL DEFAULT 0,
    BatDauLuc DATETIME NULL,
    KetThucLuc DATETIME NULL,
    TrangThai VARCHAR(20) NOT NULL DEFAULT 'inactive',
    NgayTao TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    NgayCapNhat TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (MaBanner),
    KEY banners_status_order_index (TrangThai, ThuTuSapXep),
    CONSTRAINT banners_manager_fk FOREIGN KEY (MaNguoiQuanLy) REFERENCES users (MaNguoiDung) ON DELETE SET NULL,
    CONSTRAINT banners_status_check CHECK (TrangThai IN ('active', 'inactive'))
) ENGINE=InnoDB;

CREATE TABLE admin_audit_logs (
    MaNhatKyQuanTri BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    MaNguoiThucHien BIGINT UNSIGNED NULL,
    HanhDong VARCHAR(100) NOT NULL,
    LoaiDoiTuong VARCHAR(100) NOT NULL,
    MaDoiTuong BIGINT UNSIGNED NULL,
    TomTat VARCHAR(500) NOT NULL,
    DiaChiIP VARCHAR(45) NULL,
    NgayTao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (MaNhatKyQuanTri),
    KEY admin_audit_actor_date_index (MaNguoiThucHien, NgayTao),
    KEY admin_audit_subject_index (LoaiDoiTuong, MaDoiTuong),
    CONSTRAINT admin_audit_actor_fk FOREIGN KEY (MaNguoiThucHien) REFERENCES users (MaNguoiDung) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Initial catalog categories.
INSERT IGNORE INTO categories (TenDanhMuc, DuongDan, MoTa, ThuTuSapXep, TrangThai)
VALUES
    ('MoHinh', 'mo-hinh', 'MoHinh suu tam', 1, 'active'),
    ('TheBai', 'the-bai', 'TheBai suu tam', 2, 'active'),
    ('PhuKien', 'phu-kien', 'PhuKien cho mo hinh va the bai', 3, 'active');

-- Initial RBAC roles and permissions. Create the first admin user separately
-- with a securely hashed password; never store a plaintext password here.
INSERT IGNORE INTO roles (TenVaiTro, TenHienThi)
VALUES
    ('administrator', 'QuanTriVien'),
    ('catalog_manager', 'QuanLySanPham'),
    ('order_staff', 'NhanVienDonHang'),
    ('content_staff', 'QuanLyNoiDung'),
    ('analyst', 'NhanVienThongKe'),
    ('customer', 'KhachHang');

INSERT IGNORE INTO permissions (TenQuyen, TenHienThi)
VALUES
    ('products.view', 'XemSanPham'),
    ('products.create', 'ThemSanPham'),
    ('products.update', 'SuaSanPham'),
    ('products.delete', 'AnSanPham'),
    ('categories.manage', 'QuanLyDanhMuc'),
    ('orders.view', 'XemDonHang'),
    ('orders.update-status', 'CapNhatTrangThaiDonHang'),
    ('customers.view', 'XemKhachHang'),
    ('customers.lock', 'KhoaTaiKhoanKhachHang'),
    ('coupons.manage', 'QuanLyKhuyenMai'),
    ('content.manage', 'QuanLyBaiVietVaBanner'),
    ('reports.view', 'XemBaoCao'),
    ('users.manage', 'QuanLyTaiKhoanVaPhanQuyen'),
    ('orders.view-own', 'XemDonHangCuaMinh');

INSERT IGNORE INTO role_permissions (MaVaiTro, MaQuyen)
SELECT r.MaVaiTro, p.MaQuyen
FROM roles r
CROSS JOIN permissions p
WHERE r.TenVaiTro = 'administrator';

INSERT IGNORE INTO role_permissions (MaVaiTro, MaQuyen)
SELECT r.MaVaiTro, p.MaQuyen
FROM roles r
JOIN permissions p ON p.TenQuyen IN (
    'products.view', 'products.create', 'products.update',
    'products.delete', 'categories.manage'
)
WHERE r.TenVaiTro = 'catalog_manager';

INSERT IGNORE INTO role_permissions (MaVaiTro, MaQuyen)
SELECT r.MaVaiTro, p.MaQuyen
FROM roles r
JOIN permissions p ON p.TenQuyen IN (
    'orders.view', 'orders.update-status', 'customers.view'
)
WHERE r.TenVaiTro = 'order_staff';

INSERT IGNORE INTO role_permissions (MaVaiTro, MaQuyen)
SELECT r.MaVaiTro, p.MaQuyen
FROM roles r
JOIN permissions p ON p.TenQuyen = 'content.manage'
WHERE r.TenVaiTro = 'content_staff';

INSERT IGNORE INTO role_permissions (MaVaiTro, MaQuyen)
SELECT r.MaVaiTro, p.MaQuyen
FROM roles r
JOIN permissions p ON p.TenQuyen = 'reports.view'
WHERE r.TenVaiTro = 'analyst';

INSERT IGNORE INTO role_permissions (MaVaiTro, MaQuyen)
SELECT r.MaVaiTro, p.MaQuyen
FROM roles r
JOIN permissions p ON p.TenQuyen = 'orders.view-own'
WHERE r.TenVaiTro = 'customer';
