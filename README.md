# Collector-s-market

## Database and system design

The initial database schema and design documents for the model and trading-card store are available here:

- [MySQL schema](database/schema.sql) - creates the `model_store` database, tables, foreign keys, and initial categories/roles/permissions.
- [System design](docs/system-design.md) - core architecture, access control, and product/category administration design.
- [Admin UI prototype](docs/admin-products-prototype.html) - clickable, browser-only screens for dashboard, products, categories, orders, customers, reviews, and settings.
- [Data architecture and ERD](.github/modernize/assessment/engines/facts/data-architecture.md) - proposed entity model and Mermaid ER diagram.

### Create the database

Requires MySQL 8.0 or a compatible MariaDB version. From the repository root, run:

```sh
mysql -u root -p < database/schema.sql
```

The script does not create an administrator account or include a default password. Create the first admin securely in the application, and keep real database credentials in an untracked `.env` file.

### Cấu hình biến môi trường bổ sung (`.env`)

Sao chép các biến mẫu từ [`.env.example`](.env.example) vào file `.env` trên máy chạy (không đưa `.env` thật lên Git):

1. **Chế độ Demo Quên mật khẩu (A6)**:
   - Mặc định **TẮT**: `PASSWORD_RESET_DEMO=false` (hệ thống chỉ gửi liên kết đặt lại mật khẩu qua SMTP / Mailtrap / log theo cấu hình `MAIL_*`).
   - Khi cần **BẬT** để trình diễn trực tiếp trên màn hình (khi chưa cấu hình SMTP): đổi thành `PASSWORD_RESET_DEMO=true` trong `.env` (sau đó chạy `php artisan config:clear`). Khi bật, trang `/quen-mat-khau` sẽ hiển thị thêm khung thông báo chứa liên kết đặt lại mật khẩu.
2. **Thông tin tài khoản ngân hàng nhận chuyển khoản (A5)**:
   - Cấu hình qua `config/storefront.php` đọc từ các biến `.env`:
     - `BANK_TRANSFER_BANK_NAME`
     - `BANK_TRANSFER_ACCOUNT_NUMBER`
     - `BANK_TRANSFER_ACCOUNT_HOLDER`
     - `BANK_TRANSFER_BRANCH`
   - Trong `.env.example` chỉ lưu giá trị giả; điền thông tin tài khoản thật vào `.env` nội bộ khi chạy demo.

---

## Hướng dẫn cài đặt nhanh & nạp dữ liệu mẫu (D4 – TV3)

### 1. Các bước cài đặt nhanh

```bash
# Bước 1: Cài đặt thư viện PHP (nếu chạy trên khung Laravel đầy đủ)
composer install

# Bước 2: Tạo file .env từ .env.example và sinh APP_KEY
cp .env.example .env
php artisan key:generate

# Bước 3: Khởi tạo CSDL lõi của nhóm (TV1)
mysql -u root -p < database/schema.sql

# Bước 4: Chạy các migration bổ sung của TV3
# (Tạo bảng password_reset_tokens cho A6 và cập nhật 7 trạng thái đơn hàng cho A5/B3/A7)
php artisan migrate

# Bước 5: Nạp dữ liệu mẫu (Tài khoản, Sản phẩm, Khuyến mãi, Mã giảm giá, Đơn hàng mẫu)
php artisan db:seed --class=Database\\Seeders\\Tv3DemoSeeder

# Bước 6: Khởi chạy máy chủ phát triển
php artisan serve
```

### 2. Danh sách tài khoản mẫu (Seed Data – `Tv3DemoSeeder`)

| Vai trò | Email đăng nhập | Mật khẩu | Trạng thái | Ghi chú |
| :--- | :--- | :--- | :--- | :--- |
| **Quản trị viên (`administrator`)** | `admin@collectorsmarket.local` | `Admin@123456` | `active` | Toàn quyền quản trị (B3, B4, B5...) |
| **Nhân viên đơn hàng (`order_staff`)** | `orderstaff@collectorsmarket.local` | `Staff@123456` | `active` | Quyền xem/cập nhật đơn hàng & xem khách hàng |
| **Khách hàng 1 (`customer`)** | `khachhang1@collectorsmarket.local` | `Customer@123456` | `active` | Có sẵn đơn `CM202611130001` (`preparing`) và `CM202611130002` (`shipping`) |
| **Khách hàng 2 (`customer`)** | `khachhang2@collectorsmarket.local` | `Customer@123456` | `active` | Có sẵn đơn `CM202611130003` (`completed`) |
| **Khách hàng bị khóa (`customer`)** | `khachkhoa@collectorsmarket.local` | `Customer@123456` | `locked` | Dùng để kiểm thử chặn đăng nhập tài khoản bị khóa (A6, B4) |

### 3. Mã giảm giá mẫu (`coupons`)

- `COLLECTOR10`: Giảm **10%** (tối đa `100.000 đ`) cho đơn hàng có tạm tính từ `300.000 đ`.
- `GIAM50K`: Giảm cố định **50.000 đ** cho đơn hàng có tạm tính từ `500.000 đ`.
- `EXPIRED20`: Mã giảm giá đã hết hạn (dùng để kiểm thử thông báo lỗi mã hết hạn).

### 4. Chạy kiểm thử tự động (Feature Tests – TV3)

```bash
php artisan test --filter=AuthTest
php artisan test --filter=PromotionAndCouponAdminTest
php artisan test --filter=CartAndCheckoutTest
php artisan test --filter=OrderManagementAdminTest
php artisan test --filter=AccountAndOrderHistoryTest
php artisan test --filter=CustomerManagementAdminTest
```

