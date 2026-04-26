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
        Schema::table('coop_settings', function (Blueprint $table) {
            $table->string('type')->default('string')->after('value');
            $table->string('group')->nullable()->after('type');
            $table->string('label')->nullable()->after('group');
            $table->text('description')->nullable()->after('label');
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
