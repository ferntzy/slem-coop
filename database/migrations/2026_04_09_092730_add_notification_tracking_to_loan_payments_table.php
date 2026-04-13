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
        Schema::table('loan_payments', function (Blueprint $table) {
            $table->timestamp('due_reminder_sent_at')->nullable()->after('status');
            $table->integer('overdue_notice_level')->nullable()->after('due_reminder_sent_at'); // 1, 7, 30 days
            $table->timestamp('overdue_notice_sent_at')->nullable()->after('overdue_notice_level');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_payments', function (Blueprint $table) {
            $table->dropColumn([
                'due_reminder_sent_at',
                'overdue_notice_level',
                'overdue_notice_sent_at',
            ]);
        });
    }
};
