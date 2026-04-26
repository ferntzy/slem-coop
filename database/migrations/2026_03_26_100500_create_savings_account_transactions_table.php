<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('savings_account_transactions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('profile_id');
            $table->foreign('profile_id')
                ->references('profile_id')
                ->on('profiles')
                ->cascadeOnDelete();
            $table->string('savings_type_id');
            $table->integer('terms')->nullable();
            $table->decimal('deposit', 14, 2)->default(0)->nullable();
            $table->decimal('withdrawal', 14, 2)->default(0)->nullable();
            $table->string('type', 30)->index();
            $table->text('notes')->nullable();
            $table->string('proof_of_transaction')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('savings_account_transactions');
    }
};
