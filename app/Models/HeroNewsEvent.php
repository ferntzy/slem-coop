<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroNewsEvent extends Model
{
    protected $fillable = [
        'hero_badge',
        'hero_header',
        'hero_paragraph',
    ];

    public static function getHeroNewsEvent(): static
    {
        $heronewsevent = static::find(1);

        if (! $heronewsevent) {
            $heronewsevent = static::create([
                'hero_badge' => 'Stay Updated',
                'hero_header' => 'News & Events',
                'hero_paragraph' => 'Stay informed about the latest happenings in our cooperative community.',
            ]);
        }

        return $heronewsevent;
    }

    public static function set(string $field, mixed $value): static
    {
        $heroNewsEvent = static::find(1);

        if (! $heroNewsEvent) {
            $heroNewsEvent = static::create([
                'hero_badge' => 'Stay Updated',
                'hero_header' => 'News & Events',
                'hero_paragraph' => 'Stay informed about the latest happenings in our cooperative community',
            ]);
        }
        if (in_array($field, ['hero_badge', 'hero_header', 'hero_paragraph'])) {
            $heroNewsEvent->update([$field => $value]);
        }

        return $heroNewsEvent;
    }
}
