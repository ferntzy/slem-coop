<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Orientation extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'video_path',
        'pass_threshold',
        'allow_retakes',
        'max_attempts',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'allow_retakes' => 'boolean',
        'is_active'     => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function questions()
    {
        return $this->hasMany(OrientationQuestion::class)->orderBy('order');
    }

    public function assessments()
    {
        return $this->hasMany(OrientationAssessment::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by', 'user_id');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by', 'user_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public static function getActive(): ?self
    {
        return self::where('is_active', true)->latest()->first();
    }

    public function getTotalPointsAttribute(): int
    {
        return $this->questions->sum('points');
    }
}