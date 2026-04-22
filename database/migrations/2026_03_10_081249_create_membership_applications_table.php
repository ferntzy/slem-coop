<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Step 1: Create table without foreign keys
        Schema::create('membership_applications', function (Blueprint $table) {
            $table->id();

            // Reference profile
            $table->unsignedBigInteger('profile_id');

            // Membership info
            $table->unsignedBigInteger('membership_type_id');
            $table->date('application_date')->default(now());
            $table->enum('status', ['pending', 'approved', 'rejected', 'needs_review'])->default('pending');
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();

            // Documents
            $table->string('id_document')->nullable();
            $table->string('proof_of_income')->nullable();
            $table->text('other_documents')->nullable();

            // Admin tracking
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by'); // matches users.user_id
            $table->unsignedBigInteger('updated_by')->nullable();

            $table->timestamps();
        });

        // Step 2: Add foreign keys after table creation
        Schema::table('membership_applications', function (Blueprint $table) {
            $table->foreign('profile_id')
                ->references('profile_id')
                ->on('profiles')
                ->onDelete('cascade');

            $table->foreign('membership_type_id')
                ->references('membership_type_id')
                ->on('membership_types')
                ->onDelete('cascade');

            $table->foreign('created_by')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('updated_by')
                ->references('user_id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('membership_applications');
    }
};
