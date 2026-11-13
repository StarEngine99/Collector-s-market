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
