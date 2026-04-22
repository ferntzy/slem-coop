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
        Schema::table('loan_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('loan_applications', 'collateral_type')) {
                $table->string('collateral_type')->nullable()->after('collateral_status');
            }
        });
    }

    public function down()
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn(['collateral_type', 'collateral_document']);
        });
    }
};
