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
        Schema::create('loan_type_requirements', function (Blueprint $table) {
            $table->bigIncrements('loan_type_requirement_id');
            $table->unsignedBigInteger('loan_type_id')->index();

            $table->string('code', 50);
            $table->string('label', 150);
            $table->boolean('is_required')->default(true);
            $table->unsignedSmallInteger('sort_order')->default(0);

            $table->timestamps();

            $table->unique(['loan_type_id', 'code'], 'loan_type_req_unique');

            $table->foreign('loan_type_id')
                ->references('loan_type_id')->on('loan_types')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_type_requirements');
    }
};
