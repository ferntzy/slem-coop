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
        if (! Schema::hasColumn('profiles', 'branch_id')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('roles_id')
                    ->constrained('branches', 'branch_id')
                    ->nullOnDelete();
            });
        }

        DB::statement(<<<'SQL'
            update profiles
            inner join staff_details on profiles.profile_id = staff_details.profile_id
            set profiles.branch_id = staff_details.branch_id
            where profiles.branch_id is null
        SQL);

        if (Schema::hasColumn('staff_details', 'branch_id')) {
            Schema::table('staff_details', function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasColumn('staff_details', 'branch_id')) {
            Schema::table('staff_details', function (Blueprint $table) {
                $table->foreignId('branch_id')
                    ->nullable()
                    ->after('staff_detailscol')
                    ->constrained('branches', 'branch_id')
                    ->nullOnDelete();
            });
        }

        DB::statement(<<<'SQL'
            update staff_details
            inner join profiles on profiles.profile_id = staff_details.profile_id
            set staff_details.branch_id = profiles.branch_id
            where staff_details.branch_id is null
        SQL);

        if (Schema::hasColumn('profiles', 'branch_id')) {
            Schema::table('profiles', function (Blueprint $table) {
                $table->dropConstrainedForeignId('branch_id');
            });
        }
    }
};
