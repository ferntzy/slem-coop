<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContactPageSetting extends Model
{
    protected $table = 'contact_page_settings';

    protected $fillable = [
        'hero_badge',
        'hero_title',
        'hero_subtitle',
        'phone',
        'email',
        'address',
        'hours',
        'facebook_url',
        'twitter_url',
        'instagram_url',
        'linkedin_url',
        'maps_embed_url',
        'maps_lat',
        'maps_lng',
        'branches',
    ];

    protected $casts = [
        'branches' => 'array',
    ];

    /**
     * Always fetch a FRESH row from DB — never use firstOrCreate
     * so that Filament saves are always reflected immediately.
     */
    public static function getSetting(): static
    {
        $setting = static::find(1);

        if (! $setting) {
            $setting = static::create([
                'id' => 1,
                'hero_badge' => 'Get in Touch',
                'hero_title' => 'Contact Us',
                'hero_subtitle' => "Have questions? We're here to help. Reach out to us through any of our channels.",
                'phone' => '(123) 456-7890',
                'email' => 'info@community-coop.com',
                'address' => '123 Main Street, Downtown',
                'hours' => 'Mon-Fri 9AM-5PM',
                'facebook_url' => '',
                'twitter_url' => '',
                'instagram_url' => '',
                'linkedin_url' => '',
                'maps_embed_url' => '',
                'maps_lat' => '',
                'maps_lng' => '',
                'branches' => [
                    ['name' => 'Main Branch',  'address' => '123 Main Street, Downtown',  'phone' => '(123) 456-7890', 'hours' => 'Mon-Fri 9AM-5PM'],
                    ['name' => 'North Branch', 'address' => '456 North Avenue, Northside', 'phone' => '(123) 456-7891', 'hours' => 'Mon-Sat 9AM-4PM'],
                    ['name' => 'South Branch', 'address' => '789 South Road, Southside',   'phone' => '(123) 456-7892', 'hours' => 'Mon-Fri 10AM-6PM'],
                ],
            ]);
        }

        return $setting;
    }
}
