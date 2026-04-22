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
        Schema::table('loan_applications', function (Blueprint $table) {
            if (! Schema::hasColumn('loan_applications', 'manager_approved_at')) {
                $table->timestamp('manager_approved_at')->nullable()->after('approved_at');
            }

            if (! Schema::hasColumn('loan_applications', 'manager_approved_by_user_id')) {
                $table->unsignedBigInteger('manager_approved_by_user_id')->nullable()->after('manager_approved_at');
            }

            if (! Schema::hasColumn('loan_applications', 'admin_approved_at')) {
                $table->timestamp('admin_approved_at')->nullable()->after('manager_approved_by_user_id');
            }

            if (! Schema::hasColumn('loan_applications', 'admin_approved_by_user_id')) {
                $table->unsignedBigInteger('admin_approved_by_user_id')->nullable()->after('admin_approved_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_applications', function (Blueprint $table) {
            $table->dropColumn([
                'manager_approved_at',
                'manager_approved_by_user_id',
                'admin_approved_at',
                'admin_approved_by_user_id',
            ]);
        });
    }
};
