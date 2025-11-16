<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdatePaymentMethodEnumFinanceEntries extends Migration
{
    public function up()
    {
        // Adjust the enum values for payment_method to only allow Cash and Gcash
        // Use raw statement to support altering enum directly (MySQL). If you use Postgres,
        // adjust accordingly.
        DB::statement("ALTER TABLE `finance_entries` MODIFY `payment_method` ENUM('Cash','Gcash') NOT NULL DEFAULT 'Cash'");
    }

    public function down()
    {
        // Revert back to the previous set of allowed values
        DB::statement("ALTER TABLE `finance_entries` MODIFY `payment_method` ENUM('Credit Card','Bank Transfer','Cash','Gift Card','Other') NOT NULL DEFAULT 'Cash'");
    }
}
