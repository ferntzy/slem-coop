<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('member_co_makers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_detail_id')->constrained('member_details')->cascadeOnDelete();

            $table->string('full_name')->nullable();
            $table->string('relationship')->nullable();
            $table->string('contact_number')->nullable();
            $table->string('address')->nullable();
            $table->string('occupation')->nullable();
            $table->string('employer_name')->nullable();
            $table->decimal('monthly_income', 12, 2)->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_co_makers');
    }
};
