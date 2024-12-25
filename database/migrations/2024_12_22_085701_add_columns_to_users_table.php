<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('username')->unique()->nullable()->after('id'); // Thêm username, unique, nullable, sau id
            $table->string('avatar')->nullable()->after('password'); // Thêm avatar, nullable, sau password
            $table->decimal('commission', 10, 2)->default(0)->after('avatar'); // Thêm commission, default 0, sau avatar
            $table->integer('level')->default(1)->after('commission'); // Thêm level, default 1, sau commission
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['username', 'avatar', 'commission', 'level']); // Xóa các cột
        });
    }
};
