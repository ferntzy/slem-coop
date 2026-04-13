<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   public function up()
{
    Schema::table('loan_payments', function (Blueprint $table) {
        $table->unsignedBigInteger('loan_account_id')->nullable();
        // Optionally add a foreign key:
        // $table->foreign('loan_account_id')->references('loan_account_id')->on('loan_accounts');
    });
}

public function down()
{
    Schema::table('loan_payments', function (Blueprint $table) {
        $table->dropColumn('loan_account_id');
    });
}
};
