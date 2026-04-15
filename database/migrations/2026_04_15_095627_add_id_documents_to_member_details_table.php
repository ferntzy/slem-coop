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
            $table->string('id_document_front')->nullable()->after('id_number');
            $table->string('id_document_back')->nullable()->after('id_document_front');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('member_details', function (Blueprint $table) {
            $table->dropColumn(['id_document_front', 'id_document_back']);
        });
    }
};
