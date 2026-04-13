<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('restructure_application_status_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('restructure_application_id');
            $table->string('from_status')->nullable();
            $table->string('to_status');
            $table->unsignedBigInteger('changed_by_user_id');
            $table->text('reason')->nullable();
            $table->timestamp('changed_at');
            $table->timestamps();

            $table->foreign('restructure_application_id', 'fk_rasl_restructure_app')
                ->references('restructure_application_id')
                ->on('restructure_applications')
                ->onDelete('cascade');

            $table->foreign('changed_by_user_id', 'fk_rasl_changed_by')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('restructure_application_status_logs');
    }
};