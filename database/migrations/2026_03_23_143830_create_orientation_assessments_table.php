<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orientation_assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('membership_application_id')
                ->constrained('membership_applications')
                ->cascadeOnDelete();
            $table->foreignId('orientation_id')
                ->constrained('orientations');
            $table->unsignedTinyInteger('attempt_number')->default(1);
            $table->unsignedTinyInteger('score')->nullable();        // 0–100 percentage
            $table->boolean('passed')->default(false);
            $table->timestamp('video_watched_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->string('certificate_path')->nullable();
            $table->timestamp('certificate_generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orientation_assessments');
    }
};
