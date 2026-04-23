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
        Schema::table('coop_fees', function (Blueprint $table) {
            // Drop the old unique constraint
            $table->dropUnique(['type', 'group', 'status']);

            // Add optional name column to differentiate same-type fees
            $table->string('name')->nullable()->after('type');
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
