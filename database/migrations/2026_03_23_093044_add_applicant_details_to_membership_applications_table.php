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
        Schema::table('membership_applications', function (Blueprint $table) {
            // Make profile_id nullable so applications can exist without a profile initially
            if (Schema::hasColumn('membership_applications', 'profile_id')) {
                try {
                    $table->unsignedBigInteger('profile_id')->nullable()->change();
                } catch (Exception $e) {
                    // Already nullable
                }
            }

            // Add applicant details columns (stored directly in the application record)
            if (! Schema::hasColumn('membership_applications', 'first_name')) {
                $table->string('first_name', 100)->after('profile_id');
            }
            if (! Schema::hasColumn('membership_applications', 'middle_name')) {
                $table->string('middle_name', 45)->nullable()->after('first_name');
            }
            if (! Schema::hasColumn('membership_applications', 'last_name')) {
                $table->string('last_name', 45)->after('middle_name');
            }
            if (! Schema::hasColumn('membership_applications', 'email')) {
                $table->string('email')->unique()->after('last_name');
            }
            if (! Schema::hasColumn('membership_applications', 'mobile_number')) {
                $table->string('mobile_number', 11)->after('email');
            }
            if (! Schema::hasColumn('membership_applications', 'birthdate')) {
                $table->date('birthdate')->after('mobile_number');
            }
            if (! Schema::hasColumn('membership_applications', 'sex')) {
                $table->enum('sex', ['Male', 'Female'])->nullable()->after('birthdate');
            }
            if (! Schema::hasColumn('membership_applications', 'address')) {
                $table->text('address')->nullable()->after('sex');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('membership_applications', function (Blueprint $table) {
            // Drop applicant detail columns if they exist
            if (Schema::hasColumn('membership_applications', 'first_name')) {
                $table->dropColumn([
                    'first_name',
                    'middle_name',
                    'last_name',
                    'email',
                    'mobile_number',
                    'birthdate',
                    'sex',
                    'address',
                ]);
            }
        });
    }
};
