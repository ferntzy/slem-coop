<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->decimal('shared_capital_fee', 14, 2)->default(0)->after('principal_amount');
            $table->decimal('insurance_fee', 14, 2)->default(0)->after('shared_capital_fee');
            $table->decimal('processing_fee', 14, 2)->default(0)->after('insurance_fee');
            $table->decimal('coop_fee_total', 14, 2)->default(0)->after('processing_fee');
            $table->decimal('net_release_amount', 14, 2)->default(0)->after('coop_fee_total');
        });
    }

    public function down(): void
    {
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->dropColumn([
                'shared_capital_fee',
                'insurance_fee',
                'processing_fee',
                'coop_fee_total',
                'net_release_amount',
            ]);
        });
    }
};