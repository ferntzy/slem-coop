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
        // Update the source_of_income enum to match form options
        Schema::table('member_details', function (Blueprint $table) {
            $table->enum('source_of_income', [
                'Employment',
                'Business',
                'Remittance',
                'Pension/Retirement',
                'Agriculture',
                'Others',
            ])->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert to previous enum values
        Schema::table('member_details', function (Blueprint $table) {
            $table->enum('source_of_income', [
                'salary',
                'business',
                'others',
            ])->nullable()->change();
        });
    }
};
