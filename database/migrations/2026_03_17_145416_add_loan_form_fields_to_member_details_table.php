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
        Schema::table('member_details', function (Blueprint $table) {
            $table->unsignedInteger('years_in_coop')->nullable()->after('status');
            $table->unsignedInteger('dependents_count')->nullable()->after('years_in_coop');
            $table->unsignedInteger('children_in_school_count')->nullable()->after('dependents_count');
        });
    }

    public function down(): void
    {
        Schema::table('member_details', function (Blueprint $table) {
            $table->dropColumn([
                'years_in_coop',
                'dependents_count',
                'children_in_school_count',
            ]);
        });
    }
};
