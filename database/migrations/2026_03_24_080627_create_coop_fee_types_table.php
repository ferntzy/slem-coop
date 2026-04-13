<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create the new fee types table
        Schema::create('coop_fee_types', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('key')->unique();
            $table->text('description')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });

        // 2. Seed the default types from the old enum values
        DB::table('coop_fee_types')->insert([
            ['name' => 'Shared Capital', 'key' => 'shared_capital', 'description'=> 'Fee for shared capital contributions', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Insurance',      'key' => 'insurance',      'description'=> 'Fee for insurance coverage', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Processing Fee', 'key' => 'processing_fee', 'description'=> 'Fee for processing loan applications', 'status' => 'active', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // 3. Add the foreign key column to coop_fees
        Schema::table('coop_fees', function (Blueprint $table) {
            $table->foreignId('coop_fee_type_id')->nullable()->constrained('coop_fee_types')->nullOnDelete();
        });

        // 4. Migrate existing type data to the new foreign key
        DB::statement("
            UPDATE coop_fees cf
            JOIN coop_fee_types cft ON cft.key = cf.type
            SET cf.coop_fee_type_id = cft.id
        ");


    }

    public function down(): void
    {
        Schema::table('coop_fees', function (Blueprint $table) {
            $table->dropForeign(['coop_fee_type_id']);
            $table->dropColumn('coop_fee_type_id');
            $table->enum('type', ['shared_capital', 'insurance', 'processing_fee']);
        });

        Schema::dropIfExists('coop_fee_types');
    }
};
