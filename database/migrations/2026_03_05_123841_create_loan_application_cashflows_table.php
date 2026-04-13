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
       Schema::create('loan_application_cashflows', function (Blueprint $table) {
            $table->bigIncrements('loan_application_cashflow_id');

            $table->unsignedBigInteger('loan_application_id')->index();

            // income | expense | debt
            $table->enum('row_type', ['income', 'expense', 'debt'])->index();

            $table->string('label', 150);
            $table->decimal('amount', 14, 2);

            $table->text('notes')->nullable();

            $table->timestamps();

            $table->foreign('loan_application_id')
                ->references('loan_application_id')->on('loan_applications')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_application_cashflows');
    }
};
