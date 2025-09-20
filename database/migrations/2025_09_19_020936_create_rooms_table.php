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
        Schema::create('rooms', function (Blueprint $table) {
            $table->id();
            $table->enum('category', ['individual', 'master', 'common']);
            $table->string('room_number');
            $table->enum('status', ['available', 'maintenance'])->default('available');
            $table->timestamps();
            
            // Ensure unique combination of category and room_number
            $table->unique(['category', 'room_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
