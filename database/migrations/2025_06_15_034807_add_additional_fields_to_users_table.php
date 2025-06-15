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
        Schema::table('users', function (Blueprint $table) {
            // Menambahkan kolom baru jika belum ada
            if (!Schema::hasColumn('users', 'phone_number')) {
                $table->string('phone_number')->nullable()->after('email');
            }
            if (!Schema::hasColumn('users', 'nik')) {
                $table->string('nik')->unique()->nullable()->after('phone_number');
            }
            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable()->after('nik');
            }
            if (!Schema::hasColumn('users', 'city')) {
                $table->string('city')->nullable()->after('address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Menghapus kolom jika rollback
            $table->dropColumn(['phone_number', 'nik', 'address', 'city']);
        });
    }
};

