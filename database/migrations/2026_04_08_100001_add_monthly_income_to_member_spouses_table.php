<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_spouses', function (Blueprint $table) {
            if (!Schema::hasColumn('member_spouses', 'monthly_income')) {
                $table->decimal('monthly_income', 14, 2)->nullable()->after('tin');
            }
        });
    }

    public function down(): void
    {
        Schema::table('member_spouses', function (Blueprint $table) {
            if (Schema::hasColumn('member_spouses', 'monthly_income')) {
                $table->dropColumn('monthly_income');
            }
        });
    }
};
