<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('finance_entries', function (Blueprint $table) {
            $table->text('decline_reason')->nullable()->after('reference_notes');
            $table->timestamp('declined_at')->nullable()->after('decline_reason');
        });
    }

    public function down(): void
    {
        Schema::table('finance_entries', function (Blueprint $table) {
            $table->dropColumn(['decline_reason', 'declined_at']);
        });
    }
};
