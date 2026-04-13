<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coop_fees', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['shared_capital', 'insurance', 'processing_fee']);
            $table->decimal('amount', 10, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->boolean('is_percentage')->default(false);
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->string('group')->default('Coop Fees');
            $table->timestamps();

            $table->unique(['type', 'group', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coop_fees');
    }
};
