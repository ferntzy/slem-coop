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
        Schema::create('loan_accounts', function (Blueprint $table) {
    $table->id('loan_account_id');

    $table->foreignId('loan_application_id')
        ->constrained('loan_applications', 'loan_application_id');

    $table->decimal('principal_amount', 14, 2);
    $table->decimal('interest_rate', 5, 2);

    $table->integer('term_months');

    $table->date('release_date');
    $table->date('maturity_date');

    $table->decimal('monthly_amortization', 14, 2);

    $table->decimal('balance', 14, 2);

    $table->enum('status', [
        'Active',
        'Completed',
        'Defaulted',
        'Restructured'
    ])->default('Active');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_accounts');
    }
};
