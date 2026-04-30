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
        Schema::table('profiles', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('roles_id')
                ->constrained('branches', 'branch_id')
                ->nullOnDelete();
        });

        DB::table('profiles')
            ->join('staff_details', 'profiles.profile_id', '=', 'staff_details.profile_id')
            ->whereNull('profiles.branch_id')
            ->update(['branch_id' => DB::raw('staff_details.branch_id')]);

        Schema::table('staff_details', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('staff_details', function (Blueprint $table) {
            $table->foreignId('branch_id')
                ->nullable()
                ->after('staff_detailscol')
                ->constrained('branches', 'branch_id')
                ->nullOnDelete();
        });

        DB::table('staff_details')
            ->join('profiles', 'profiles.profile_id', '=', 'staff_details.profile_id')
            ->whereNull('staff_details.branch_id')
            ->update(['branch_id' => DB::raw('profiles.branch_id')]);

        Schema::table('profiles', function (Blueprint $table) {
            $table->dropConstrainedForeignId('branch_id');
        });
    }
};
