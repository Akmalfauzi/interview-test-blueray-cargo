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
        Schema::create('trackings', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('order_id');

            $table->string('biteship_status_code')->nullable(); // Kode status dari Biteship (misal: 'POD')
            $table->string('status_description'); // Deskripsi status (misal: "Paket telah diterima oleh kurir")
            $table->string('location')->nullable(); // Lokasi terkini paket (jika tersedia)
            $table->timestamp('tracked_at'); // Waktu status ini dicatat oleh Biteship/Kurir
            $table->text('notes')->nullable(); // Catatan tambahan dari tracking event
            $table->json('raw_biteship_payload')->nullable(); // Untuk menyimpan payload JSON mentah dari Biteship jika diperlukan

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trackings');
    }
};
