<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Cập nhật CHECK constraint của cột `orders.TrangThai` để hỗ trợ 7 trạng thái đơn hàng đã chốt:
     * 1. preparing  (Đang chuẩn bị hàng)
     * 2. prepared   (Đã chuẩn bị hàng)
     * 3. shipping   (Đang giao hàng)
     * 4. in_transit (Đơn hàng trên đường tới)
     * 5. delivering (Đang giao đến bạn)
     * 6. completed  (Hoàn tất)
     * 7. cancelled  (Đã hủy)
     * (Giữ tương thích ngược với các giá trị cũ trong schema.sql).
     */
    public function up(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        try {
            DB::statement('ALTER TABLE orders DROP CHECK orders_status_check');
        } catch (\Throwable) {
            // Bỏ qua nếu constraint chưa tồn tại hoặc phiên bản MariaDB dùng cú pháp khác.
            try {
                DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_status_check');
            } catch (\Throwable) {
            }
        }

        DB::statement("
            ALTER TABLE orders
            ADD CONSTRAINT orders_status_check
            CHECK (TrangThai IN (
                'preparing',
                'prepared',
                'shipping',
                'in_transit',
                'delivering',
                'completed',
                'cancelled',
                'pending',
                'confirmed',
                'processing',
                'shipped'
            ))
        ");
    }

    /**
     * Khôi phục CHECK constraint ban đầu của schema.sql.
     */
    public function down(): void
    {
        if (!Schema::hasTable('orders')) {
            return;
        }

        $driver = Schema::getConnection()->getDriverName();
        if (!in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        try {
            DB::statement('ALTER TABLE orders DROP CHECK orders_status_check');
        } catch (\Throwable) {
            try {
                DB::statement('ALTER TABLE orders DROP CONSTRAINT orders_status_check');
            } catch (\Throwable) {
            }
        }

        DB::statement("
            ALTER TABLE orders
            ADD CONSTRAINT orders_status_check
            CHECK (TrangThai IN ('pending', 'confirmed', 'processing', 'shipped', 'completed', 'cancelled'))
        ");
    }
};
