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
        Schema::table('sent_emails', function (Blueprint $table) {
            $table->string('type')->default('notification')->after('mailable_class');
            $table->string('reference_type')->nullable()->after('type');
            $table->unsignedBigInteger('reference_id')->nullable()->after('reference_type');
            $table->text('body')->nullable()->after('reference_id');
            $table->index(['type', 'email', 'created_at'], 'sent_emails_type_email_created_index');
            $table->unique(['email', 'type', 'reference_type', 'reference_id'], 'sent_emails_unique_notification');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sent_emails', function (Blueprint $table) {
            $table->dropUnique('sent_emails_unique_notification');
            $table->dropIndex('sent_emails_type_email_created_index');
            $table->dropColumn(['body', 'reference_id', 'reference_type', 'type']);
        });
    }
};
