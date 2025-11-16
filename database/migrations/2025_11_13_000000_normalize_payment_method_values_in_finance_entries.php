<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Prepare `finance_entries.payment_method` data so it fits the upcoming enum change.
     */
    public function up(): void
    {
        // Map every legacy value to 'Cash' first because the current enum does not allow 'Gcash'.
        DB::statement("UPDATE `finance_entries` SET `payment_method` = 'Cash' WHERE `payment_method` NOT IN ('Cash','Gcash')");
    }

    /**
     * Cannot safely restore original literal values once normalized.
     */
    public function down(): void
    {
        // intentionally left blank
    }
};
