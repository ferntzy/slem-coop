<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->unsignedBigInteger('penalty_rule_id')
                ->nullable()
                ->after('term_months');

            $table->foreign('penalty_rule_id')
                ->references('id')
                ->on('penalty_rules')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropForeign(['penalty_rule_id']);
            $table->dropColumn('penalty_rule_id');
        });
    }
};
