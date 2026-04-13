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
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->enum('application_type', ['New', 'Restructure', 'Reloan'])
                ->default('New')
                ->after('loan_type_id')
                ->index();

            $table->unsignedBigInteger('parent_loan_account_id')
                ->nullable()
                ->after('application_type')
                ->index();

            $table->foreign('parent_loan_account_id')
                ->references('loan_account_id')->on('loan_accounts')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropForeign(['parent_loan_account_id']);
            $table->dropColumn(['application_type', 'parent_loan_account_id']);
        });
    }
};
