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
         Schema::create('loan_application_documents', function (Blueprint $table) {
            $table->bigIncrements('loan_application_document_id');

            $table->unsignedBigInteger('loan_application_id')->index();

            // For requirement uploads, this matches loan_product_requirements.code (e.g. collateral_proof).
            // For generated PDFs, you can use codes like: consent_form, loan_agreement.
            $table->string('code', 50)->nullable()->index();

            // requirement | consent | agreement | other
            $table->string('document_type', 30)->default('requirement')->index();

            $table->boolean('is_generated')->default(false)->index();

            $table->string('file_path', 255);
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->unsignedBigInteger('uploaded_by_user_id')->nullable()->index();

            $table->timestamps();

            $table->foreign('loan_application_id')
                ->references('loan_application_id')->on('loan_applications')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->foreign('uploaded_by_user_id')
                ->references('user_id')->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_application_documents');
    }
};
