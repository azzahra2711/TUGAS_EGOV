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
        Schema::create('booking_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->foreignId('seat_id')->nullable()->constrained('seats')->onDelete('set null'); // Nullable if not seat-specific
            $table->foreignId('seat_type_id')->constrained('seat_types')->onDelete('cascade');
            $table->integer('quantity'); // Jumlah tiket untuk jenis ini
            $table->decimal('price_per_unit', 10, 2); // Harga saat booking
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('booking_details');
    }
};

