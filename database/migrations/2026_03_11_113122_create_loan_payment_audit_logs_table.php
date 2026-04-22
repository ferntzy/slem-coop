<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loan_payment_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_payment_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->enum('action', ['edit', 'void']);
            $table->text('reason')->nullable();
            $table->json('before')->nullable();
            $table->json('after')->nullable();
            $table->timestamps();

            $table->foreign('loan_payment_id')
                ->references('loan_payment_id')
                ->on('loan_payments')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_payment_audit_logs');
    }
};
