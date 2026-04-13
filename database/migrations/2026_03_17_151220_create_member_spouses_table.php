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
        Schema::create('member_spouses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('member_detail_id')->constrained('member_details')->cascadeOnDelete();

            $table->string('full_name')->nullable();
            $table->date('birthdate')->nullable();
            $table->string('occupation')->nullable();
            $table->string('employer_name')->nullable();
            $table->string('employer_address')->nullable();
            $table->string('source_of_income')->nullable();
            $table->string('tin')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('member_spouses');
    }
};
