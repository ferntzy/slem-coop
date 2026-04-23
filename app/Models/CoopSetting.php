<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CoopSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
        'label',
        'description',
    ];

    public function getCastedValueAttribute(): mixed
    {
        return match ($this->type) {
            'integer' => (int) $this->value,
            'float' => (float) $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->casted_value : $default;
    }

    public static function set(string $key, mixed $value, ?string $type = null): static
    {
        if ($type === null) {
            $type = match (true) {
                is_array($value) => 'json',
                is_bool($value) => 'boolean',
                is_int($value) => 'integer',
                is_float($value) => 'float',
                default => 'string',
            };
        }

        return static::updateOrCreate(
            ['key' => $key],
            [
                'value' => is_array($value) ? json_encode($value) : (string) $value,
                'type' => $type,
            ]
        );
    }

    public static function group(string $group): Collection
    {
        return static::where('group', $group)
            ->get()
            ->mapWithKeys(fn (self $s) => [$s->key => $s->casted_value]);
    }
}
