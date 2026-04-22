<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('member_details', function (Blueprint $table) {
            if (! Schema::hasColumn('member_details', 'zip_code')) {
                $table->string('zip_code')->nullable()->after('province');
            }
        });
    }

    public function down(): void
    {
        Schema::table('member_details', function (Blueprint $table) {
            if (Schema::hasColumn('member_details', 'zip_code')) {
                $table->dropColumn('zip_code');
            }
        });
    }
};
