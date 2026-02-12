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
        Schema::table('payments', function (Blueprint $table) {
            $table->string('midtrans_transaction_id')->nullable()->after('external_id');
            $table->json('midtrans_response')->nullable()->after('metadata');
            $table->dropIndex(['xendit_invoice_id']);
            $table->dropColumn(['xendit_invoice_id', 'xendit_payment_id', 'xendit_response']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('xendit_invoice_id')->nullable();
            $table->string('xendit_payment_id')->nullable();
            $table->json('xendit_response')->nullable();
            $table->dropColumn(['midtrans_transaction_id', 'midtrans_response']);
            $table->index('xendit_invoice_id');
        });
    }
};
