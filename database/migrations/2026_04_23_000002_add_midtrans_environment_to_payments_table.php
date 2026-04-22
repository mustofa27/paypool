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
            $table->string('midtrans_environment', 20)
                ->default('sandbox')
                ->after('app_id');

            $table->index('midtrans_environment');
            $table->index(['app_id', 'midtrans_environment']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['app_id', 'midtrans_environment']);
            $table->dropIndex(['midtrans_environment']);
            $table->dropColumn('midtrans_environment');
        });
    }
};
