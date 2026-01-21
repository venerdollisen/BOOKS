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
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->foreignId('department_id')->nullable()->after('account_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->after('department_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('subsidiary_account_id')->nullable()->after('project_id')->constrained('subsidiary_accounts')->cascadeOnDelete();
            
            $table->index('department_id');
            $table->index('project_id');
            $table->index('subsidiary_account_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            $table->dropForeignKeyIfExists(['department_id']);
            $table->dropForeignKeyIfExists(['project_id']);
            $table->dropForeignKeyIfExists(['subsidiary_account_id']);
            $table->dropIndex(['department_id']);
            $table->dropIndex(['project_id']);
            $table->dropIndex(['subsidiary_account_id']);
            $table->dropColumn(['department_id', 'project_id', 'subsidiary_account_id']);
        });
    }
};
