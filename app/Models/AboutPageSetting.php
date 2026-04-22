<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AboutPageSetting extends Model
{
    protected $table = 'about_page_settings';

    protected $fillable = [
        'hero_badge',
        'hero_title',
        'hero_subtitle',
        'vision',
        'mission',
        'history',
        'core_values',
        'testimonials',
        'board_members',
        'org_general_assembly',
        'org_board_of_directors',
        'org_management_team',
        'org_operational_staff',
    ];

    protected $casts = [
        'history' => 'array',
        'core_values' => 'array',
        'testimonials' => 'array',
        'board_members' => 'array',
    ];

    /**
     * Always work with a single settings row (singleton pattern).
     */
    public static function getSetting(): static
    {
        return static::firstOrCreate(
            ['id' => 1],
            [
                'hero_badge' => 'Our Story',
                'hero_title' => 'About Us',
                'hero_subtitle' => 'Building a stronger community through cooperative banking since 2001',
                'vision' => 'To be the leading community cooperative, empowering members through innovative financial solutions and fostering sustainable economic development for generations to come.',
                'mission' => 'To provide accessible, member-focused financial services that promote economic well-being, strengthen community bonds, and create lasting value through cooperative principles.',
                'history' => [
                    ['year' => '2001', 'title' => 'Foundation',             'desc' => 'Community Cooperative was founded by 50 visionary members.'],
                    ['year' => '2010', 'title' => 'Growth & Expansion',     'desc' => 'Reached 5,000 members and opened our second branch.'],
                    ['year' => '2020', 'title' => 'Digital Transformation', 'desc' => 'Launched digital banking services and mobile app.'],
                    ['year' => '2026', 'title' => 'Today',                  'desc' => 'Proudly serving over 10,000 members with ₱50M+ in assets.'],
                ],
                'core_values' => [
                    ['icon' => 'Target',     'title' => 'Integrity',  'description' => 'We uphold the highest standards of honesty and transparency.'],
                    ['icon' => 'Users',      'title' => 'Community',  'description' => 'We strengthen our community through mutual support.'],
                    ['icon' => 'TrendingUp', 'title' => 'Growth',     'description' => 'We foster financial growth for all our members.'],
                    ['icon' => 'Award',      'title' => 'Excellence', 'description' => 'We deliver exceptional service and value.'],
                ],
                'testimonials' => [
                    ['name' => 'Ana Lopez',     'feedback' => 'This cooperative helped me grow my small business through affordable loans and excellent support.'],
                    ['name' => 'Michael Cruz',  'feedback' => 'Their digital services make managing my savings easy and convenient.'],
                    ['name' => 'Sofia Reyes',   'feedback' => 'I truly feel valued as a member. The service is always friendly and professional.'],
                ],
                'board_members' => [
                    ['name' => 'John Smith',     'position' => 'Chairman',      'photo' => ''],
                    ['name' => 'Maria Garcia',   'position' => 'Vice Chairman',  'photo' => ''],
                    ['name' => 'Robert Johnson', 'position' => 'Treasurer',      'photo' => ''],
                    ['name' => 'Lisa Chen',      'position' => 'Secretary',      'photo' => ''],
                ],
                'org_general_assembly' => 'The highest governing body composed of all cooperative members. Responsible for major decisions and policy approvals.',
                'org_board_of_directors' => 'Provides strategic leadership, policy direction, and ensures the cooperative operates according to its mission and vision.',
                'org_management_team' => 'Oversees daily operations, financial management, and implementation of board policies.',
                'org_operational_staff' => 'Handles member services, loan processing, transactions, and administrative functions.',
            ]
        );
    }
}
