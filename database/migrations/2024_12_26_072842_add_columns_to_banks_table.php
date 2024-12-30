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
        Schema::table('banks', function (Blueprint $table) {
            $table->string('address')->nullable()->after('account_number'); // Thêm cột address, nullable, sau cột ...
            $table->string('site')->nullable()->after('address'); // Thêm cột site, nullable, sau cột address
            $table->string('bankaccount')->nullable()->after('site'); // Thêm cột bankaccount, nullable, sau cột site
            $table->string('tel')->nullable()->after('bankaccount'); // Thêm cột tel, nullable, sau cột bankaccount
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('banks', function (Blueprint $table) {
            $table->dropColumn(['address', 'site', 'bankaccount', 'tel']);
        });
    }
};