<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penalty_rules', function (Blueprint $table) {
            $table->id();
            $table->string('name');                                          // e.g. "Standard Late Payment Penalty"
            $table->text('description')->nullable();
            $table->enum('frequency', ['daily', 'monthly']);                 // how often the penalty accrues
            $table->enum('value_type', ['percentage', 'fixed']);             // % of outstanding or fixed amount
            $table->decimal('value', 10, 2);                                 // the rate or fixed amount
            $table->unsignedInteger('grace_period_days')->default(0);        // days before penalty kicks in
            $table->decimal('max_penalty_cap', 10, 2)->nullable();           // optional max cap (null = no cap)
            $table->boolean('is_escalating')->default(true);                 // compounds over time
            $table->unsignedInteger('escalation_interval')->nullable();      // every N days/months, rate increases
            $table->decimal('escalation_increment', 5, 2)->nullable();       // how much value increases per interval
            $table->decimal('escalation_max_value', 10, 2)->nullable();      // ceiling for escalated rate
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penalty_rules');
    }
};
