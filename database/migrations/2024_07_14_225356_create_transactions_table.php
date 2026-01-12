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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->text('transaction_code'); //transaction code
            $table->foreignId('user_id');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreignId('bengkel_id');
            $table->foreign('bengkel_id')->references('id')->on('bengkels')->onDelete('cascade');
            $table->foreignId('booking_id')->nullable();
            $table->foreign('booking_id')->references('id')->on('bookings')->onDelete('cascade');
            $table->foreignId('product_id')->nullable();
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            $table->foreignId('layanan_id')->nullable();
            $table->foreign('layanan_id')->references('id')->on('layanans')->onDelete('cascade');
            $table->integer('administrasi')->nullable();
            $table->enum('payment_status', array('pending', 'success', 'failed', 'expired'))->nullable();
            $table->enum('shipping_status', array('Pending', 'Disiapkan', 'Dikirim', 'Selesai'))->nullable();
            $table->integer('ongkir')->nullable();
            $table->bigInteger('grand_total');
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
        Schema::dropIfExists('transactions');
    }
};
