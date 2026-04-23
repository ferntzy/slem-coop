<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('member_details', function (Blueprint $table) {
            DB::statement("ALTER TABLE member_details MODIFY status ENUM('Active', 'Inactive', 'Delinquent', 'Dormant') NULL");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_details', function (Blueprint $table) {
            DB::statement("UPDATE member_details SET status = 'Inactive' WHERE status = 'Dormant'");
            DB::statement("ALTER TABLE member_details MODIFY status ENUM('Active', 'Inactive', 'Delinquent') NULL");
        });
    }
};
