<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    /**
     * Get a setting value by key, with optional default.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::rememberForever("system_setting_{$key}", function () use ($key, $default) {
            $setting = static::find($key);

            return $setting?->value ?? $default;
        });
    }

    /**
     * Set a setting value by key and clear its cache.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget("system_setting_{$key}");
    }

    /**
     * Clear all cached settings.
     */
    public static function clearCache(): void
    {
        foreach (['app_name', 'logo', 'favicon', 'primary_color', 'font'] as $key) {
            Cache::forget("system_setting_{$key}");
        }
    }
}
