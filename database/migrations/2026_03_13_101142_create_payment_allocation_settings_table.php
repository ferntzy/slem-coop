<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_allocation_settings', function (Blueprint $table) {
            $table->id();

            $table->boolean('allow_partial')->default(true);
            $table->boolean('allow_advance')->default(true);
            $table->boolean('allow_overpayment')->default(true);
            $table->boolean('auto_apply')->default(false);

            $table->boolean('allow_void')->default(true);
            $table->boolean('require_void_reason')->default(true);

            $table->boolean('allow_edit')->default(true);
            $table->boolean('require_edit_reason')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_allocation_settings');
    }
};
