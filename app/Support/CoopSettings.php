<?php

namespace App\Support;

use App\Models\CoopSetting;

class CoopSettings
{
    public static function get(string $key, $default = null)
    {
        return cache()->remember("coop_setting:$key", 300, function () use ($key, $default) {
            return CoopSetting::query()->where('key', $key)->value('value') ?? $default;
        });
    }

    public static function forget(string $key): void
    {
        cache()->forget("coop_setting:$key");
    }
}
