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
        Schema::table('bookings', function (Blueprint $table) {
            // Drop the existing enum column and recreate it with the new value
            $table->dropColumn('status');
        });
        
        Schema::table('bookings', function (Blueprint $table) {
            // Add the enum column back with the new 'rejected' status
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed', 'rejected'])->default('pending');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            // Drop the updated enum column
            $table->dropColumn('status');
        });
        
        Schema::table('bookings', function (Blueprint $table) {
            // Restore the original enum column
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'completed'])->default('pending');
        });
    }
};
