<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('collection_and_postings', function (Blueprint $table) {
            $table->unsignedInteger('schedule_period')->nullable()->after('loan_account_id');
            $table->date('scheduled_due_date')->nullable()->after('schedule_period');
        });
    }

    public function down(): void
    {
        Schema::table('collection_and_postings', function (Blueprint $table) {
            $table->dropColumn([
                'schedule_period',
                'scheduled_due_date',
            ]);
        });
    }
};
