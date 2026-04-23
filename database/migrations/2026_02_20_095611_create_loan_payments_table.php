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
        Schema::create('loan_payments', function (Blueprint $table) {
            $table->id('loan_payment_id');

            $table->foreignId('loan_application_id')
                ->constrained('loan_applications', 'loan_application_id');

            $table->date('payment_date');

            $table->decimal('amount_paid', 14, 2);
            $table->decimal('principal_paid', 14, 2);
            $table->decimal('interest_paid', 14, 2);

            $table->decimal('penalty_paid', 14, 2)->default(0);

            $table->decimal('remaining_balance', 14, 2);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_payments');
    }
};
