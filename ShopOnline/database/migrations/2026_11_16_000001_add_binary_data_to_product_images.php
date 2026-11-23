<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_images', 'DuLieuAnh')) {
            DB::statement('ALTER TABLE product_images ADD COLUMN DuLieuAnh MEDIUMBLOB NULL');
        }

        if (!Schema::hasColumn('product_images', 'LoaiNoiDung')) {
            DB::statement('ALTER TABLE product_images ADD COLUMN LoaiNoiDung VARCHAR(100) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_images', 'LoaiNoiDung')) {
            DB::statement('ALTER TABLE product_images DROP COLUMN LoaiNoiDung');
        }

        if (Schema::hasColumn('product_images', 'DuLieuAnh')) {
            DB::statement('ALTER TABLE product_images DROP COLUMN DuLieuAnh');
        }
    }
};
