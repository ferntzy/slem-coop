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
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->unsignedBigInteger('profile_id')->after('loan_account_id');

            $table->foreign('profile_id')
                ->references('profile_id')
                ->on('profiles')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('loan_accounts', function (Blueprint $table) {
            $table->dropForeign(['profile_id']);
            $table->dropColumn('profile_id');
        });
    }
};
