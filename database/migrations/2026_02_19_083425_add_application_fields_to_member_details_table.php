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
        Schema::table('member_details', function (Blueprint $table) {
            // Employment
            $table->string('occupation', 100)->nullable()->after('member_no');
            $table->string('employer_name', 150)->nullable()->after('occupation');
            $table->string('monthly_income_range', 50)->nullable()->after('employer_name');

            // Identification
            $table->string('id_type', 50)->nullable()->after('monthly_income_range');
            $table->string('id_number', 100)->nullable()->after('id_type');

            // Emergency contact
            $table->string('emergency_full_name', 150)->nullable()->after('id_number');
            $table->string('emergency_phone', 50)->nullable()->after('emergency_full_name');
            $table->string('emergency_relationship', 50)->nullable()->after('emergency_phone');

            // Signature (store file path)
            $table->string('signature_path', 255)->nullable()->after('emergency_relationship');
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_details', function (Blueprint $table) {
            //
        });
    }
};
