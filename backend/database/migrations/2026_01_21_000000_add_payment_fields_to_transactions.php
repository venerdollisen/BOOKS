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
        Schema::table('transactions', function (Blueprint $table) {
            $table->string('check_number')->nullable()->after('notes');
            $table->string('bank')->nullable()->after('check_number');
            $table->string('billing_number')->nullable()->after('bank');
            $table->string('collection_receipt')->nullable()->after('billing_number');
            $table->string('delivery_receipt')->nullable()->after('collection_receipt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['check_number', 'bank', 'billing_number', 'collection_receipt', 'delivery_receipt']);
        });
    }
};
