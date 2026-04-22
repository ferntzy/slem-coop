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
        Schema::create('loan_application_status_logs', function (Blueprint $table) {
            $table->bigIncrements('loan_application_status_log_id');

            $table->unsignedBigInteger('loan_application_id')->index();

            // Store as string to avoid enum-coupling.
            // Your current application statuses include: Pending, Under Review, Approved, Rejected, Cancelled
            $table->string('from_status', 30)->nullable()->index();
            $table->string('to_status', 30)->index();

            $table->unsignedBigInteger('changed_by_user_id')->nullable()->index();

            // reason for rejection/cancellation, etc.
            $table->text('reason')->nullable();

            $table->timestamp('changed_at')->useCurrent()->index();

            $table->foreign('loan_application_id')
                ->references('loan_application_id')->on('loan_applications')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('changed_by_user_id')
                ->references('user_id')->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_application_status_logs');
    }
};
