<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tạo bảng lưu token đặt lại mật khẩu cho chức năng Quên mật khẩu (A6).
     */
    public function up(): void
    {
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table): void {
                $table->string('Email', 190)->primary();
                $table->string('Token', 255);
                $table->timestamp('NgayTao')->nullable();
            });
        }
    }

    /**
     * Xóa bảng password_reset_tokens khi rollback.
     */
    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
    }
};

