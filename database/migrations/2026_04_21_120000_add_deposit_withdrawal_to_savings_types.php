<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('savings_types', function (Blueprint $table) {
            if (! Schema::hasColumn('savings_types', 'deposit_allowed')) {
                $table->boolean('deposit_allowed')->default(true)->after('interest_rate');
            }

            if (! Schema::hasColumn('savings_types', 'withdrawal_allowed')) {
                $table->boolean('withdrawal_allowed')->default(true)->after('deposit_allowed');
            }
        });
    }

    public function down(): void
    {
        Schema::table('savings_types', function (Blueprint $table) {
            if (Schema::hasColumn('savings_types', 'withdrawal_allowed')) {
                $table->dropColumn('withdrawal_allowed');
            }

            if (Schema::hasColumn('savings_types', 'deposit_allowed')) {
                $table->dropColumn('deposit_allowed');
            }
        });
    }
};
