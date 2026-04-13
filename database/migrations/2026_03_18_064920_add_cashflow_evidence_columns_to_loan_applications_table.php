<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
   public function up(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->json('cashflow_documents')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            if (Schema::hasColumn('loan_applications', 'cashflow_documents')) {
                $table->dropColumn('cashflow_documents');
            }
        });

    }
};
