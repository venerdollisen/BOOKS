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
            if (!Schema::hasColumn('transactions', 'check_date')) {
                $table->date('check_date')->nullable()->after('check_number');
            }
            if (!Schema::hasColumn('transactions', 'payee_description')) {
                $table->text('payee_description')->nullable()->after('description');
            }
            if (!Schema::hasColumn('transactions', 'attached_file')) {
                $table->string('attached_file')->nullable()->after('receipt_image');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            if (Schema::hasColumn('transactions', 'check_date')) {
                $table->dropColumn('check_date');
            }
            if (Schema::hasColumn('transactions', 'payee_description')) {
                $table->dropColumn('payee_description');
            }
            if (Schema::hasColumn('transactions', 'attached_file')) {
                $table->dropColumn('attached_file');
            }
        });
    }
};
