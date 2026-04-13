<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('membership_applications', function (Blueprint $table) {
            $table->string('civil_status')->nullable()->after('sex');

            $table->string('id_type')->nullable()->after('id_document');
            $table->string('id_number')->nullable()->after('id_type');

            $table->string('house_no')->nullable()->after('address');
            $table->string('street_barangay')->nullable()->after('house_no');
            $table->string('municipality')->nullable()->after('street_barangay');
            $table->string('province')->nullable()->after('municipality');
            $table->string('zip_code')->nullable()->after('province');

            $table->string('occupation')->nullable()->after('zip_code');
            $table->string('employer_name')->nullable()->after('occupation');
            $table->string('monthly_income_range')->nullable()->after('employer_name');
            $table->string('source_of_income')->nullable()->after('monthly_income_range');
            $table->decimal('monthly_income', 14, 2)->nullable()->after('source_of_income');
            $table->integer('years_in_business')->nullable()->after('monthly_income');

            $table->string('emergency_full_name')->nullable()->after('years_in_business');
            $table->string('emergency_phone')->nullable()->after('emergency_full_name');
            $table->string('emergency_relationship')->nullable()->after('emergency_phone');

            $table->integer('dependents_count')->nullable()->after('emergency_relationship');
            $table->integer('children_in_school_count')->nullable()->after('dependents_count');

            $table->boolean('orientation_zoom_attended')->default(false)->after('remarks');
            $table->boolean('orientation_video_completed')->default(false)->after('orientation_zoom_attended');
            $table->boolean('orientation_assessment_passed')->default(false)->after('orientation_video_completed');
            $table->boolean('orientation_certificate_generated')->default(false)->after('orientation_assessment_passed');

            $table->string('signature_path')->nullable()->after('other_documents');
        });
    }

    public function down(): void
    {
        Schema::table('membership_applications', function (Blueprint $table) {
            $table->dropColumn([
                'civil_status',
                'id_type',
                'id_number',
                'house_no',
                'street_barangay',
                'municipality',
                'province',
                'zip_code',
                'occupation',
                'employer_name',
                'monthly_income_range',
                'source_of_income',
                'monthly_income',
                'years_in_business',
                'emergency_full_name',
                'emergency_phone',
                'emergency_relationship',
                'dependents_count',
                'children_in_school_count',
                'orientation_zoom_attended',
                'orientation_video_completed',
                'orientation_assessment_passed',
                'orientation_certificate_generated',
                'signature_path',
            ]);
        });
    }
};
