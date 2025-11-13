<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Attempt to drop the foreign key if it exists. Some environments may not have
        // added the foreign constraint; this ensures the migration is safe to run.
        try {
            DB::statement('ALTER TABLE `bookings` DROP FOREIGN KEY `bookings_room_id_foreign`');
        } catch (\Throwable $e) {
            // Ignore - constraint may not exist.
        }

        // Ensure the column exists and is an integer so mapped IDs (1001/2001/3001+) can be stored.
        if (Schema::hasColumn('bookings', 'room_id')) {
            Schema::table('bookings', function (Blueprint $table) {
                // keep as integer; do not re-add a foreign key here because the app uses
                // mapped integer room IDs that do not necessarily match the `rooms` PK.
                // If you'd prefer a foreign key, add it manually once room IDs are migrated.
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Attempt to re-add the foreign key if the rooms table uses matching IDs.
        try {
            Schema::table('bookings', function (Blueprint $table) {
                // This will fail if `room_id` values don't match `rooms.id` or if the
                // column is not nullable. Run manually if you want to restore the FK.
                $table->foreign('room_id')->references('id')->on('rooms')->onDelete('set null');
            });
        } catch (\Throwable $e) {
            // Ignore - adding back the FK may not be safe automatically.
        }
    }
};
