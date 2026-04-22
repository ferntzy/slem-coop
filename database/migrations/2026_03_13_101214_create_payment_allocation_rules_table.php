<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocation_rules', function (Blueprint $table) {
            $table->id();

            $table->foreignId('payment_allocation_setting_id')
                ->constrained('payment_allocation_settings')
                ->cascadeOnDelete();

            $table->string('component'); // 'interest', 'principal', 'penalty'
            $table->unsignedTinyInteger('priority'); // 1, 2, 3

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocation_rules');
    }
};
