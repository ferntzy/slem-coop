<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('loan_application_collstats')) {

            Schema::create('loan_application_collstats', function (Blueprint $table) {
                $table->id('collstat_id');
                $table->unsignedBigInteger('loan_application_id');
                $table->string('from_status');
                $table->string('to_status');
                $table->unsignedBigInteger('changed_by_user_id');
                $table->text('reason')->nullable();
                $table->timestamp('changed_at')->useCurrent();
            });

        }
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_application_collstats');
    }
};
