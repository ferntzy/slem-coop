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
         Schema::create('collection_and_postings', function (Blueprint $table) {
            $table->id();
            $table->string('loan_number', 50);
            $table->string('member_name', 150);
            $table->decimal('amount_paid', 14, 2);
            $table->date('payment_date');
            $table->enum('payment_method', ['Cash', 'Bank Transfer', 'Bank Deposit', 'Check']);
            $table->string('reference_number', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('file_path')->nullable();
            $table->string('original_file_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('document_type', ['Proof of Payment (General)', 'Bank Transfer Confirmation', 'Deposit Slip', 'Official Receipt', 'GCash / E-Wallet Screenshot', 'Other']);
            $table->enum('status', ['Posted', 'Draft', 'Void']);
            $table->unsignedBigInteger('posted_by_user_id');
            $table->text('audit_trail')->nullable();
            $table->timestamps();

            $table->foreign('posted_by_user_id')->references('user_id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
