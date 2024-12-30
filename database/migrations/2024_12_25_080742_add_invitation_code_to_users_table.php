<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            // $table->string('invitation_code')->nullable(); // Cho phép null nếu không bắt buộc ngay lập tức
            $table->foreign('invitation_code')->references('code')->on('invitations')->onDelete('set null'); // Khóa ngoại đến bảng invitations
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['invitation_code']);
            $table->dropColumn('invitation_code');
        });
    }
};