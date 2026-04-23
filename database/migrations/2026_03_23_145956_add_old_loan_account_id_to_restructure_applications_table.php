<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('restructure_applications', function (Blueprint $table) {
            $table->unsignedBigInteger('old_loan_account_id')->nullable()->after('loan_application_id');
        });
    }

    public function down(): void
    {
        Schema::table('restructure_applications', function (Blueprint $table) {
            $table->dropColumn('old_loan_account_id');
        });
    }
};
