# Thiết kế nền tảng website bán mô hình và thẻ bài

> Bản thiết kế đề xuất theo file phân công. Hiện thư mục chưa có mã nguồn; đây không phải mô tả một hệ thống đã được triển khai.

## 1. Phạm vi thiết kế

- Nền tảng: Laravel + MySQL, ứng dụng monolith phân module để phù hợp tiến độ đồ án và dễ tích hợp.
- Phần TV1: CSDL, lõi ứng dụng, đăng nhập/phân quyền quản trị, CRUD sản phẩm/danh mục, quy trình GitHub và hướng dẫn triển khai.
- Các module khác có thể tích hợp sau: storefront, giỏ hàng/đơn hàng, khách hàng, khuyến mãi, bài viết/banner và báo cáo.
- Lược đồ chi tiết và ERD nằm trong [data-architecture.md](../.github/modernize/assessment/engines/facts/data-architecture.md).
- File SQL có thể import để tạo database và dữ liệu khởi tạo nằm ở [schema.sql](../database/schema.sql).

## 1.1. Tạo database MySQL

Chạy `database/schema.sql` bằng MySQL Workbench (mở file và Execute) hoặc import từ terminal:

```sh
mysql -u root -p < database/schema.sql
```

Script tạo database `model_store`, các bảng/khóa ngoại, danh mục ban đầu và vai trò/quyền mẫu. Tạo tài khoản quản trị riêng bằng ứng dụng với mật khẩu đã hash; script không tạo tài khoản có mật khẩu mặc định. Nếu kết nối ứng dụng Laravel, cấu hình thông tin `DB_*` trong `.env` của máy chạy, không commit `.env` thật.

## 2. Kiến trúc lõi

```text
Trình duyệt
  -> Laravel routes / middleware
  -> Controllers (nhận request, trả response)
  -> Form Requests (xác thực dữ liệu)
  -> Policies / Gates (ủy quyền theo hành động và đối tượng)
  -> Services (nghiệp vụ, transaction)
  -> Eloquent Models / MySQL
  -> Blade views + Bootstrap
```

Tổ chức theo module nghiệp vụ trong cùng ứng dụng:

| Module | Trách nhiệm chính |
|---|---|
| Identity & Access | Người dùng, phiên đăng nhập, vai trò, quyền |
| Catalog | Danh mục, sản phẩm, ảnh, tồn kho |
| Cart & Orders | Giỏ hàng, đơn hàng, dòng hàng, thanh toán |
| Promotions | Mã giảm giá và điều kiện sử dụng |
| Content | Bài viết và banner |
| Reporting | Tổng hợp doanh thu/tồn kho từ dữ liệu Orders và Catalog |

Controller không chứa truy vấn/logic nghiệp vụ phức tạp. Các thao tác ghi nhiều bảng được đặt trong service và dùng transaction. Mọi kiểm tra quyền phải chạy ở phía máy chủ; ẩn nút trên giao diện không thay thế authorization.

## 3. Phân quyền

Dùng RBAC: `users` ↔ `roles` ↔ `permissions`, qua các bảng nối. Tên quyền có dạng `products.view`, `products.create`, `products.update`, `products.delete`, `categories.manage`, `orders.update-status`, `customers.lock`, `coupons.manage`, `content.manage`, `reports.view`, `users.manage`.

| Vai trò đề xuất | Quyền |
|---|---|
| `administrator` | Toàn quyền; quản lý tài khoản nội bộ, vai trò/quyền và cấu hình |
| `catalog_manager` | Xem/thêm/sửa sản phẩm, danh mục và ảnh; không quản lý tài khoản |
| `order_staff` | Xem đơn và cập nhật trạng thái theo quy trình; xem thông tin giao hàng cần thiết |
| `content_staff` | Quản lý bài viết/banner |
| `analyst` | Chỉ xem báo cáo được phép, không sửa dữ liệu |
| `customer` | Quản lý thông tin của chính mình, giỏ hàng và đơn hàng thuộc sở hữu |

Khởi tạo admin qua seeder hoặc lệnh thiết lập có mật khẩu cấu hình an toàn; không commit tài khoản/mật khẩu dùng thật. Dùng Laravel authentication, hash mật khẩu, CSRF protection, session regeneration khi đăng nhập và rate limit cho đăng nhập/quên mật khẩu. Policy phải kiểm tra quyền sở hữu đối với hồ sơ, địa chỉ và đơn hàng.

## 4. Quản trị sản phẩm và danh mục

### Sản phẩm

- Danh sách: phân trang; tìm theo tên/SKU; lọc danh mục, trạng thái và khoảng giá; hiển thị tồn kho.
- Tạo/sửa: tên, slug, SKU, danh mục, mô tả, giá, tồn kho, trạng thái và nhiều ảnh; xác thực ở Form Request và kiểm tra quyền ở Policy.
- Ảnh: kiểm tra MIME/kích thước, đổi tên file, lưu trên filesystem hoặc object storage; chỉ lưu đường dẫn trong DB; xóa/thay ảnh phải xử lý file cũ an toàn.
- Xóa: ưu tiên ẩn/ngừng bán hoặc soft delete; không xóa cứng sản phẩm đã nằm trong đơn.
- Tồn kho: chỉnh số lượng có quyền hạn và ghi nhận lý do/thao tác; checkout phải kiểm tra lại tồn ở server.

### Danh mục

- CRUD tên, slug, danh mục cha, thứ tự hiển thị và trạng thái.
- Slug duy nhất; chống tự chọn làm cha và chu trình cha-con.
- Không xóa danh mục còn sản phẩm đang hoạt động hoặc danh mục con; hướng dẫn admin chuyển sản phẩm trước khi xóa.
- Có thể seed các danh mục ban đầu: Mô hình, Thẻ bài, Phụ kiện.

### Luồng lỗi và audit

Trả lỗi xác thực theo trường; báo rõ lỗi trùng SKU/slug, ảnh không hợp lệ, hoặc danh mục đang được sử dụng. Ghi audit log cho thay đổi giá, tồn kho, trạng thái sản phẩm/danh mục và quyền tài khoản; không ghi mật khẩu hoặc token.

## 5. Quản lý mã nguồn mở

- Tạo repository công khai trên GitHub; trước khi public, rà soát lịch sử Git để chắc chắn không có `.env`, mật khẩu, token, dữ liệu cá nhân hoặc file dump.
- Đề xuất giấy phép MIT theo tài liệu phân công; cả nhóm cần đồng thuận về quyền phát hành/đóng góp trước khi thêm LICENSE.
- Nhánh `main` luôn ổn định; `develop` tích hợp; mỗi công việc đi từ nhánh `feature/<mã>-<ten>` hoặc `fix/<mo-ta>`.
- Không push trực tiếp `main`; mỗi thay đổi tạo Pull Request vào `develop`, có ít nhất một thành viên khác review, kiểm tra chạy được và liên kết Issue.
- Commit nhỏ, mô tả rõ, ví dụ `feat(catalog): add product images`; dùng GitHub Issues, labels và milestones để theo dõi các mã A/B/C/D trong phân công.
- README cần có yêu cầu môi trường, cài đặt, cấu hình `.env.example`, migrate/seed, chạy test, ảnh demo và cấu trúc module. Thêm `CONTRIBUTING.md`, giấy phép và danh sách thư viện/giấy phép tương ứng.

## 6. Triển khai demo

1. Dùng Docker Compose cho môi trường demo thống nhất (ứng dụng Laravel, web server và MySQL); giữ cấu hình triển khai riêng khỏi mã bí mật.
2. Lưu cấu hình nhạy cảm trong biến môi trường của môi trường chạy; không đưa `.env` thật vào Git. Chỉ cung cấp `.env.example` với giá trị giả.
3. Triển khai bản demo lên hosting/VPS có HTTPS; dùng MySQL có backup, giới hạn quyền DB, tắt debug và cấp quyền filesystem tối thiểu.
4. Pipeline phát hành: kiểm tra mã/test -> build -> migrate có backup và kế hoạch rollback -> smoke test trang chủ, đăng nhập admin, danh sách sản phẩm.
5. Seed dữ liệu mẫu không chứa dữ liệu khách hàng thật; tài khoản demo phải đổi mật khẩu khi phát hành.

Các lệnh vận hành cụ thể phụ thuộc cách nhóm chọn chạy Docker hay hosting; không commit secrets hoặc thông tin đăng nhập vào tài liệu.

## 7. Hoàn thành thiết kế

- Chốt ERD và quy ước schema trước khi các thành viên viết module; mọi thay đổi DB đi qua migration và được thông báo cho nhóm.
- Xác nhận ma trận quyền bằng tài khoản admin/staff/customer riêng.
- Kiểm tra CRUD sản phẩm/danh mục, quyền truy cập trái phép, đầu vào không hợp lệ, ảnh tải lên, tìm kiếm/phân trang và trạng thái tồn kho.
- Có hướng dẫn cài mới từ README và dữ liệu seed để thành viên khác chạy được dự án.
