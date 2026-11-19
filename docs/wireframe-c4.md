# Wireframe Layout Chung (C4)

## 1. Bố cục Người dùng — Desktop (`>= 992px`)

```text
+-------------------------------------------------------------------------+
| [Top bar] Chuyên mô hình chính hãng & thẻ bài | Giới thiệu | Liên hệ    |
+-------------------------------------------------------------------------+
| [Logo] Chợ Figure & Thẻ | Trang chủ | Mô hình v | Thẻ bài v | Phụ kiện |
|                          [Tìm mô hình, thẻ bài... (Q)] [Giỏ hàng] [ĐN]  |
+-------------------------------------------------------------------------+
|                                                                         |
|   [Thông báo flash success / error nếu có]                              |
|                                                                         |
|   @yield('content')                                                     |
|   (A1: Banner + SP nổi bật + SP mới / A2: Danh mục / A3: Chi tiết...)  |
|                                                                         |
+-------------------------------------------------------------------------+
| [Cột 1: Giới thiệu shop] | [Cột 2: Danh mục SP] | [Cột 3: Chính sách]   |
|-------------------------------------------------------------------------|
| © Chợ Figure & Thẻ (MIT)             | Xây dựng bằng Laravel & BS5      |
+-------------------------------------------------------------------------+
```

## 2. Bố cục Người dùng — Mobile (`< 992px`)

```text
+-----------------------------------+
| [Top bar] Chuyên mô hình & thẻ    |
+-----------------------------------+
| [Logo] Chợ Figure & Thẻ   [≡ Menu]|
+-----------------------------------+
| (Khi mở menu ≡)                   |
| - Trang chủ                       |
| - Mô hình / Thẻ bài / Phụ kiện    |
| - [Tìm mô hình, thẻ bài...  (Q)]  |
| - [Giỏ hàng]  [Đăng nhập]         |
+-----------------------------------+
| @yield('content') (xếp 1-2 cột)   |
+-----------------------------------+
| Footer (xếp dọc theo cột)         |
+-----------------------------------+
```

## 3. Bố cục Quản trị (`layouts/admin.blade.php`) — dùng cho B6 & B7

```text
+-------------------------------------------------------------------------+
| [≡ (mobile)] Quản trị Cửa hàng                  [Xem trang bán hàng]    |
+-------------------+-----------------------------------------------------+
| Sidebar (desktop) | Nội dung chính (@yield('content'))                  |
| - Nội dung (B6)   | - Thông báo flash                                   |
|   + Bài viết      | - Bảng CRUD / Form / Biểu đồ                        |
|   + Banner        |                                                     |
| - Báo cáo (B7)    |                                                     |
|   + Doanh thu     |                                                     |
+-------------------+-----------------------------------------------------+
```
