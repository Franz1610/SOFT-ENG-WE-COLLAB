<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('bookings', 'duration_hours')) {
                $table->unsignedInteger('duration_hours')->nullable()->after('end_time');
            }
            if (!Schema::hasColumn('bookings', 'estimated_price')) {
                $table->decimal('estimated_price', 8, 2)->nullable()->after('duration_hours');
            }
        });
    }

    public function down(): void
    {
        Schema::table('bookings', function (Blueprint $table) {
            if (Schema::hasColumn('bookings', 'estimated_price')) {
                $table->dropColumn('estimated_price');
            }
            if (Schema::hasColumn('bookings', 'duration_hours')) {
                $table->dropColumn('duration_hours');
            }
        });
    }
};
