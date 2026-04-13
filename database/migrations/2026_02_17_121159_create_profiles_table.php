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
       Schema::create('profiles', function (Blueprint $table) {
        $table->id('profile_id');

        $table->string('first_name', 100);
        $table->string('middle_name', 45)->nullable();
        $table->string('last_name', 45);
        $table->string('email')->unique(); // ⭐ LOGIN EMAIL HERE
        $table->string('mobile_number', 45)->nullable();
        $table->date('birthdate')->nullable();
        $table->enum('sex', ['Male', 'Female'])->nullable();
        $table->string('address', 255)->nullable(); 

        $table->foreignId('roles_id')
            ->constrained('roles');

        $table->timestamps();
    });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
{
    Schema::dropIfExists('member_details'); // Drop dependent table first
    Schema::dropIfExists('profiles');
}
};
