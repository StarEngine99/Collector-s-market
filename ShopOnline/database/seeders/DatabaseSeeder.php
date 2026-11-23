<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Chạy toàn bộ dữ liệu mẫu của ứng dụng.
     */
    public function run(): void
    {
        $this->call([
            Tv3DemoSeeder::class,
        ]);
    }
}

