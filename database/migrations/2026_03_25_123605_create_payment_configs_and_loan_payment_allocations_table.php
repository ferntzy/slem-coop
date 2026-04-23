<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocation_configs', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Human-readable (e.g., "Penalty")
            $table->string('column_name'); // Matches your loan_applications table
            $table->integer('sort_order')->default(0); // The "Waterfall" sequence
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocation_configs');
    }
};
