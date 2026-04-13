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
        Schema::create('member_details', function (Blueprint $table) {
            $table->id('id');

            $table->unsignedBigInteger('profile_id');
            $table->foreign('profile_id')
                ->references('profile_id')
                ->on('profiles')
                ->cascadeOnDelete();

            $table->string('member_no', 45)->nullable();
            $table->text('employment_info')->nullable();
            $table->decimal('monthly_income', 14, 2)->nullable();

            // ADD THIS LINE:
            $table->unsignedBigInteger('membership_type_id');

            // THEN THE FOREIGN KEY:
            $table->foreign('membership_type_id')
                ->references('membership_type_id')
                ->on('membership_types');

            $table->foreignId('branch_id')
                ->constrained('branches', 'branch_id');

            $table->enum('status', ['Active','Inactive','Delinquent'])->nullable();

            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('member_details');
    }
};
