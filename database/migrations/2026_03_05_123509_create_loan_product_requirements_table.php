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
         Schema::create('loan_product_requirements', function (Blueprint $table) {
            $table->bigIncrements('loan_product_requirement_id');

            $table->unsignedBigInteger('loan_product_id')->index();

            // Example codes: gov_id, payslip, collateral_proof, proof_of_income
            $table->string('code', 50);
            $table->string('label', 150);

            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['loan_product_id', 'code'], 'loan_product_req_unique');

            $table->foreign('loan_product_id')
                ->references('loan_product_id')->on('loan_products')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_product_requirements');
    }
};
