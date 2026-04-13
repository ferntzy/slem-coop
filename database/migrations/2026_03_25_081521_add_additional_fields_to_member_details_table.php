<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_details', function (Blueprint $table) {

            // Source of Income (optional or required? assuming optional)
            $table->enum('source_of_income', ['salary', 'business', 'others'])
                  ->nullable();

            // Address
            $table->string('house_no')->nullable(); // REQUIRED
            $table->string('street_barangay')->nullable();
            $table->string('province')->nullable();

            // Employment / Business
           $table->integer('years_in_business')->nullable(); // REQUIRED
        });
    }

    public function down(): void
    {
        Schema::table('member_details', function (Blueprint $table) {
            $table->dropColumn([
                'source_of_income',
                'house_no',
                'street_barangay',
                'province',
                'years_in_business',
            ]);
        });
    }
};