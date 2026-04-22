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
        Schema::table('payment_allocation_rules', function (Blueprint $table) {

            if (! Schema::hasColumn('payment_allocation_rules', 'bucket')) {
                $table->string('bucket')->after('payment_allocation_setting_id');
            }

            if (! Schema::hasColumn('payment_allocation_rules', 'label')) {
                $table->string('label')->after('bucket');
            }

            if (! Schema::hasColumn('payment_allocation_rules', 'description')) {
                $table->string('description')->nullable()->after('label');
            }

            if (! Schema::hasColumn('payment_allocation_rules', 'priority')) {
                $table->integer('priority')->default(1)->after('description');
            }

            if (! Schema::hasColumn('payment_allocation_rules', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('priority');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payment_allocation_rules', function (Blueprint $table) {
            $table->dropColumn(['bucket', 'label', 'description', 'priority', 'is_active']);
        });
    }
};
