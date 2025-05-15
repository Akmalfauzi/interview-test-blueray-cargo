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
            $table->text('shipper_address_line1');
            $table->string('shipper_address_line2')->nullable(); // Alamat baris 2 opsional
            $table->string('shipper_city');
            $table->string('shipper_postal_code');
            $table->string('shipper_country_code', 2)->default('ID'); // ISO 3166-1 alpha-2 country code, default Indonesia

            // Informasi Penerima
            $table->string('consignee_name');
            $table->string('consignee_phone');
            $table->text('consignee_address_line1');
            $table->string('consignee_address_line2')->nullable(); // Alamat baris 2 opsional
            $table->string('consignee_city');
            $table->string('consignee_postal_code');
            $table->string('consignee_country_code', 2)->default('ID');

            // Detail Barang
            $table->text('item_description');
            $table->decimal('item_weight_kg', 8, 2); // Berat dalam KG, misal 10.50 KG
            $table->decimal('item_value', 15, 2); // Harga barang
            $table->integer('item_quantity')->default(1); // Jumlah barang (jika diperlukan, deskripsi hanya menyebutkan satu set detail)

            // Informasi dari Biteship
            $table->string('biteship_order_id')->nullable()->unique(); // ID order dari Biteship, unik jika ada
            $table->string('biteship_tracking_id')->nullable()->index(); // ID tracking dari Biteship, bisa diindeks
            $table->string('courier_name')->nullable(); // Nama kurir yang digunakan
            $table->string('courier_service_name')->nullable(); // Layanan kurir yang digunakan
            $table->decimal('shipping_cost', 15, 2)->nullable(); // Biaya pengiriman

            // Status Order internal aplikasi
            // Contoh: 'pending', 'processing', 'shipped', 'delivered', 'cancelled', 'failed'
            $table->string('status')->default('pending');
            $table->text('notes')->nullable(); // Catatan tambahan untuk order

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
