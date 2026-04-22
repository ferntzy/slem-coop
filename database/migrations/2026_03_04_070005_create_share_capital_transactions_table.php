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
        Schema::create('share_capital_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('profile_id')->index();
            $table->decimal('amount', 14, 2);

            // credit = add share capital, debit = deduct
            $table->enum('direction', ['credit', 'debit'])->index();

            // deposit | withdraw | adjustment
            $table->string('type', 30)->index();

            $table->date('transaction_date')->index();
            $table->string('reference_no', 50)->nullable()->index();
            $table->text('notes')->nullable();

            $table->unsignedBigInteger('posted_by_user_id')->nullable()->index();

            $table->timestamps();

            $table->foreign('profile_id')
                ->references('profile_id')->on('profiles')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->foreign('posted_by_user_id')
                ->references('user_id')->on('users')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_capital_transactions');
    }
};
