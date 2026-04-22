<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $table->decimal('amount_due', 15, 2)->default(0.00)->after('amount_paid');
            $table->decimal('carry_forward', 15, 2)->default(0.00)->after('amount_due');
            $table->enum('payment_type', ['Partial', 'Advance', 'Overpayment'])->default('Partial')->after('carry_forward');
            $table->enum('status', ['Posted', 'Void'])->default('Posted')->after('payment_type');
            $table->foreignId('posted_by')->nullable()->constrained('users', 'user_id')->nullOnDelete()->after('status');
            $table->text('remarks')->nullable()->after('posted_by');
        });
    }

    public function down(): void
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $table->dropColumn(['amount_due', 'carry_forward', 'payment_type', 'status', 'posted_by', 'remarks']);
        });
    }
};
