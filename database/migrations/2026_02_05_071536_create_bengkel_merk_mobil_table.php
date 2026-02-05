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
        Schema::create('bengkel_merk_mobil', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bengkel_id')->constrained('bengkels')->onDelete('cascade');
            $table->foreignId('merk_mobil_id')->constrained('merk_mobils')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bengkel_merk_mobil');
    }
};
