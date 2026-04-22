<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class PaymentAllocationConfig extends Model
{
    protected $fillable = [
        'name',
        'column_name',
        'sort_order',
        'is_active',
    ];

    /**
     * The "booted" method allows us to hook into model events.
     * This ensures that when a new rule is created, it gets the
     * next available priority number automatically.
     */
    protected static function booted(): void
    {
        static::creating(function ($config) {
            if (is_null($config->sort_order)) {
                $config->sort_order = static::max('sort_order') + 1;
            }
        });
    }

    /**
     * Scope to fetch only active rules in the correct order.
     * Usage: PaymentAllocationConfig::ordered()->get();
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->where('is_active', true)->orderBy('sort_order', 'asc');
    }
}
