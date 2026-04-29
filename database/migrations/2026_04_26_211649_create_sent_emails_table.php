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
        Schema::create('sent_emails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('profile_id')->nullable();
            $table->string('email');
            $table->string('subject');
            $table->string('mailable_class');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('failed_at')->nullable();
            $table->text('failure_reason')->nullable();
            $table->timestamps();

            $table->foreign('user_id', 'sent_emails_user_id_fk')
                ->references('user_id')
                ->on('users')
                ->onDelete('set null');

            $table->foreign('profile_id', 'sent_emails_profile_id_fk')
                ->references('profile_id')
                ->on('profiles')
                ->onDelete('set null');

            $table->index(['email', 'sent_at']);
            $table->index(['user_id', 'sent_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sent_emails');
    }
};
