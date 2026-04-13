<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('about_page_settings', function (Blueprint $table) {
            $table->id();

            // Hero
            $table->string('hero_badge')->default('Our Story');
            $table->string('hero_title')->default('About Us');
            $table->string('hero_subtitle')->default('Building a stronger community through cooperative banking since 2001');

            // Vision & Mission
            $table->text('vision')->nullable();
            $table->text('mission')->nullable();

            // History (JSON array of {year, title, desc})
            $table->json('history')->nullable();

            // Core Values (JSON array of {icon, title, description})
            $table->json('core_values')->nullable();

            // Testimonials (JSON array of {name, feedback})
            $table->json('testimonials')->nullable();

            // Board Members (JSON array of {name, position, photo})
            $table->json('board_members')->nullable();

            // Org Structure
            $table->text('org_general_assembly')->nullable();
            $table->text('org_board_of_directors')->nullable();
            $table->text('org_management_team')->nullable();
            $table->text('org_operational_staff')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('about_page_settings');
    }
};
