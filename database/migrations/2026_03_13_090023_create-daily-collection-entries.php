<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_collection_entries', function (Blueprint $table) {
            $table->id();

            // The Account Officer who submitted
            $table->unsignedBigInteger('ao_user_id');
            $table->foreign('ao_user_id')->references('user_id')->on('users')->onDelete('restrict');

            // Date this entry covers
            $table->date('collection_date');

            // Auto-calculated from collection_and_postings for this AO on this date
            $table->decimal('system_total', 14, 2)->default(0);
            $table->unsignedInteger('transaction_count')->default(0);

            // What the AO physically has in hand
            $table->decimal('cash_on_hand', 14, 2)->default(0);

            // Auto: system_total - cash_on_hand (can be negative)
            $table->decimal('variance', 14, 2)->default(0);

            // Submission workflow
            $table->enum('status', ['Pending', 'Submitted', 'Verified'])->default('Pending');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();

            // Branch Manager / Admin who verified
            $table->unsignedBigInteger('verified_by_user_id')->nullable();
            $table->foreign('verified_by_user_id')->references('user_id')->on('users')->onDelete('set null');

            $table->text('notes')->nullable();

            $table->timestamps();

            // One entry per AO per day
            $table->unique(['ao_user_id', 'collection_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_collection_entries');
    }
};
