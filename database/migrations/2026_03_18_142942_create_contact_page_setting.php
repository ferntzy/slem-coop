<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contact_page_settings', function (Blueprint $table) {
            $table->id();

            // Hero
            $table->string('hero_badge')->default('Get in Touch');
            $table->string('hero_title')->default('Contact Us');
            $table->string('hero_subtitle')->default("Have questions? We're here to help.");

            // Contact Info Cards
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('hours')->nullable();

            // Social Links
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->string('linkedin_url')->nullable();

            // Google Maps
            $table->text('maps_embed_url')->nullable();
            $table->string('maps_lat', 20)->nullable();
            $table->string('maps_lng', 20)->nullable();

            // Branches (JSON array of {name, address, phone, hours})
            $table->json('branches')->nullable();

            $table->timestamps();
        });

        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->text('message');
            $table->enum('status', ['unread', 'read', 'replied'])->default('unread');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contact_messages');
        Schema::dropIfExists('contact_page_settings');
    }
};
