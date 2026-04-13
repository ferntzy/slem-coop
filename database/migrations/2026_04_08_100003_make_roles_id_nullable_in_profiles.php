<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Use raw SQL to drop constraint and make roles_id nullable
        DB::statement('ALTER TABLE profiles DROP FOREIGN KEY profiles_roles_id_foreign');
        DB::statement('ALTER TABLE profiles MODIFY roles_id BIGINT UNSIGNED NULL');
        DB::statement('ALTER TABLE profiles ADD CONSTRAINT profiles_roles_id_foreign FOREIGN KEY (roles_id) REFERENCES roles(id)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revert: drop constraint, revert to non-nullable, recreate constraint
        DB::statement('ALTER TABLE profiles DROP FOREIGN KEY profiles_roles_id_foreign');
        DB::statement('ALTER TABLE profiles MODIFY roles_id BIGINT UNSIGNED NOT NULL');
        DB::statement('ALTER TABLE profiles ADD CONSTRAINT profiles_roles_id_foreign FOREIGN KEY (roles_id) REFERENCES roles(id)');
    }
};
