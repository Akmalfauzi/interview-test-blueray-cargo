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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');

            // Informasi Pengirim
            $table->string('shipper_name');
            $table->string('shipper_phone');
            $table->text('shipper_address');

            // Informasi Penerima
            $table->string('receiver_name');
            $table->string('receiver_phone');
            $table->text('receiver_address');

            $table->longText('items');

            // Informasi dari Biteship
            $table->longText('raw_biteship_payload')->nullable();

            $table->string('status')->default('pending');
            $table->text('notes')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
