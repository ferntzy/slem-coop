<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->unsignedBigInteger('reloan_from_loan_account_id')->nullable();
            $table->decimal('previous_balance', 15, 2)->default(0);
        });
    }

    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn([
                'reloan_from_loan_account_id',
                'previous_balance',
            ]);
        });
    }
};
