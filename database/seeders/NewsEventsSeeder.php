<?php

namespace Database\Seeders;

use App\Models\HeroNewsEvent;
use App\Models\News;
use App\Models\NewsEvent;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class NewsEventsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $herosections = [
                'hero_badge' => 'Stay Updated',
                'hero_header' => 'News & Events',
                'hero_paragraph' => 'Stay informed about the latest happenings in our cooperative community.',
        ];
        $events = [
            [
                'title' => 'Annual General Assembly 2026',
                'date' => 'March 15, 2026',
                'location' => 'Main Office Auditorium',
                'description' => 'Join us for our annual general assembly where we review the past year and plan for the future.',
                'category' => 'Assembly',
                'image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800'
            ],
            [
                'title' => 'Financial Literacy Seminar',
                'date' => 'March 22, 2026',
                'location' => 'Online Webinar',
                'description' => 'Learn essential financial planning skills from industry experts. Free for all members.',
                'category' => 'Education',
                'image' => 'https://images.unsplash.com/photo-1552664730-d307ca884978?w=800'
            ],
            [
                'title' => 'Community Outreach Program',
                'date' => 'April 5, 2026',
                'location' => 'Community Center',
                'description' => 'Join our cooperative in giving back to the community through various outreach activities.',
                'category' => 'Community',
                'image' => 'https://images.unsplash.com/photo-1559027615-cd4628902d4a?w=800'
            ],
        ];
        $news = [
            [
                'title'         => 'New Branch Opening Soon',
                'excerpt'       => 'We are excited to announce the opening of our East Branch in April 2026...',
                'date'          => 'February 10, 2026',
            ],
            [
                'title'         => 'Updated Interest Rates',
                'excerpt'       => 'Check out our new competitive rates on savings accounts and time deposits...',
                'date'          => 'February 1, 2026',
            ],
            [
                'title'         => 'Award Recognition',
                'excerpt'       => 'Community Cooperative receives Best Cooperative Award for outstanding service...',
                'date'          => 'January 25, 2026',
            ],
        ];

        HeroNewsEvent::updateOrCreate(
            ['hero_badge' => $herosections['hero_badge']],
            $herosections
        );

        foreach ($events as $event) {
            NewsEvent::updateOrCreate(
                ['title' => $event['title']],
                $event
            );
        }
        foreach ($news as $new) {
            News::updateOrCreate(
                ['title' => $new['title']],
                $new
            );
        }
    }
}
