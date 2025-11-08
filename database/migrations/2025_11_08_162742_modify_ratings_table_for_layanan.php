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
        Schema::table('ratings', function (Blueprint $table) {
            // 1. Ubah product_id agar boleh null
            $table->unsignedBigInteger('product_id')->nullable()->change();

            // 2. Tambahkan kolom layanan_id (yang juga boleh null)
            $table->unsignedBigInteger('layanan_id')->nullable()->after('product_id');

            // 3. Opsional: Tambahkan foreign key jika Anda mau
            // $table->foreign('layanan_id')->references('id')->on('layanans')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ratings', function (Blueprint $table) {
            // Logika untuk rollback/membatalkan
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->dropColumn('layanan_id');
        });
    }
};
