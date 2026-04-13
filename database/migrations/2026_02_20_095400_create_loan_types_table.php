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
        Schema::create('loan_types', function (Blueprint $table) {
            $table->bigIncrements('loan_type_id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('max_interest_rate', 5, 2);
            $table->integer('max_term_months');
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->string('amount_calculation_type');
            $table->decimal('amount_multiplier', 5, 2)->nullable();
            $table->boolean('is_active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_types');
    }
};
