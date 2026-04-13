<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->date('restructured_at')->nullable()->after('release_date');
        });
    }

    public function down(): void
    {
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->dropColumn('restructured_at');
        });
    }
};