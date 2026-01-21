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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('reference')->unique();
            $table->text('description')->nullable();
            $table->date('transaction_date');
            $table->enum('type', ['receipt', 'payment', 'journal', 'transfer', 'cash_receipt', 'gcash', 'bank_transfer', 'check', 'check_disbursement', 'credit_card', 'debit_card'])->index();
            $table->enum('status', ['draft', 'pending', 'approved', 'rejected'])->default('draft')->index();
            $table->decimal('amount', 12, 2);
            $table->longText('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->index(['transaction_date', 'status']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
