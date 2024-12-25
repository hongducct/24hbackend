<?php

namespace Database\Seeders;

use App\Models\OrderStatus; // Import model OrderStatus
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        OrderStatus::insert([
            ['name' => 'chưa giải quyết'],
            ['name' => 'hoàn thành'],
            ['name' => 'đóng băng'],
        ]);
    }
}
