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
       Schema::create('loan_products', function (Blueprint $table) {
            $table->id('loan_product_id');

            $table->string('name', 150);
            $table->text('description')->nullable();

            $table->decimal('interest_rate', 5, 2); // %
            $table->enum('interest_type', ['Annuity', 'Diminishing']);

            $table->decimal('min_amount', 14, 2);
            $table->decimal('max_amount', 14, 2);

            $table->integer('min_term_months');
            $table->integer('max_term_months');

            $table->boolean('requires_collateral')->default(false);
            $table->boolean('is_active')->default(true);

            $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_products');
    }
};
