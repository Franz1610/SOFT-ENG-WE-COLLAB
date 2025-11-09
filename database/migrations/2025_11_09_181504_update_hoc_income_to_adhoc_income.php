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
        // Update existing 'HOC Income' records to 'Ad-hoc Income' in transactions table
        DB::table('transactions')
            ->where('category', 'HOC Income')
            ->update(['category' => 'Ad-hoc Income']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert 'Ad-hoc Income' back to 'HOC Income' in transactions table
        DB::table('transactions')
            ->where('category', 'Ad-hoc Income')
            ->update(['category' => 'HOC Income']);
    }
};
