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
            $table->decimal('share_capital_balance', 14, 2)->default(0)->after('profile_id');
            $table->timestamp('regular_at')->nullable()->after('share_capital_balance');
            $table->index('regular_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_details', function (Blueprint $table) {
            $table->dropIndex(['regular_at']);
            $table->dropColumn(['share_capital_balance', 'regular_at']);
        });
    }
};
