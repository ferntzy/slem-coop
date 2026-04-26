<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsEvent extends Model
{
    protected $fillable = [
        'title',
        'date',
        'location',
        'description',
        'category',
        'image',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public static function getNewsEvent(): static
    {
        $newsevent = static::find(1);

        if (! $newsevent) {
            $newsevent = static::create([
                'title' => 'Annual General Assembly 2026',
                'date' => 'March 15, 2026',
                'location' => 'Main Office Auditorium',
                'description' => 'Join us for our annual general assembly where we review the past year and plan for the future.',
                'category' => 'Assembly',
                'image' => 'https://images.unsplash.com/photo-1540575467063-178a50c2df87?w=800',
            ]);
        }

        return $newsevent;
    }

    public function getImageUrlAttribute(): ?string
    {
        if ($this->image) {
            return asset('storage/'.$this->image);
        }

        return null;
    }
}
