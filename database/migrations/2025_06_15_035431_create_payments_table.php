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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained('bookings')->onDelete('cascade');
            $table->string('payment_method'); // e.g., BNI VA, BRI VA
            $table->string('transaction_id')->unique()->nullable(); // Dari gateway pembayaran
            $table->decimal('amount_paid', 10, 2);
            $table->dateTime('payment_date');
            $table->string('status')->default('Pending'); // e.g., Completed, Failed, Pending
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};

