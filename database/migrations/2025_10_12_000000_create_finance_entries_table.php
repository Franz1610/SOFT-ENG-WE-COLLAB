<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFinanceEntriesTable extends Migration
{
    public function up()
    {
        Schema::create('finance_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained('bookings');
            $table->string('customer_name');
            $table->decimal('gross_total', 10, 2);
            $table->date('transaction_date');
            $table->decimal('amount_received', 10, 2);
            $table->enum('payment_method', ['Credit Card', 'Bank Transfer', 'Cash', 'Gift Card', 'Other']);
            $table->decimal('gateway_fee', 10, 2)->default(0);
            $table->decimal('tax_collected', 10, 2)->default(0);
            $table->text('reference_notes');
            $table->decimal('net_revenue', 10, 2);
            $table->enum('status', ['Unprocessed', 'Pending Review', 'Verified'])->default('Pending Review');
            $table->foreignId('created_by');
            $table->foreignId('reviewed_by')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('finance_entries');
    }
}