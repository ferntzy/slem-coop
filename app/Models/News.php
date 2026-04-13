<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    protected $fillable = [
        'title',
        'excerpt',
        'date',
    ];

    protected $casts = [
        'date' => 'date'
    ];

    public static function getNews(): static
    {
        $news = static::find(1);

        if (!$news) {
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
        foreach ($news as $new) {
            News::updateOrCreate(
                ['title' => $new['title']],
                $new
            );
        }
        }
        return $news;
    }
}
