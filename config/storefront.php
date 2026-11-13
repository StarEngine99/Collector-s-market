<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Chế độ Demo Quên Mật Khẩu (A6)
    |--------------------------------------------------------------------------
    | Mặc định TẮT (false). Khi bật PASSWORD_RESET_DEMO=true trong file .env,
    | hệ thống vẫn thử gửi email qua SMTP/Mailtrap/log nhưng đồng thời hiển thị
    | trực tiếp liên kết đặt lại mật khẩu trên màn hình để phục vụ trình diễn.
    */
    'password_reset_demo' => (bool) env('PASSWORD_RESET_DEMO', false),

    /*
    |--------------------------------------------------------------------------
    | Thông tin tài khoản ngân hàng nhận chuyển khoản (A5)
    |--------------------------------------------------------------------------
    | Đọc hoàn toàn từ biến môi trường trong .env để không lưu thông tin thật
    | trong mã nguồn công khai.
    */
    'bank_transfer' => [
        'bank_name' => env('BANK_TRANSFER_BANK_NAME', 'Ngân hàng Demo'),
        'account_number' => env('BANK_TRANSFER_ACCOUNT_NUMBER', '0000000000'),
        'account_holder' => env('BANK_TRANSFER_ACCOUNT_HOLDER', 'CHU TAI KHOAN DEMO'),
        'branch' => env('BANK_TRANSFER_BRANCH', 'Chi nhánh Đồng Tháp'),
    ],
];

