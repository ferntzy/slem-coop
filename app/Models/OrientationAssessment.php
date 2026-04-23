<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrientationAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'membership_application_id',
        'orientation_id',
        'attempt_number',
        'score',
        'passed',
        'video_watched_at',
        'completed_at',
        'certificate_path',
        'certificate_generated_at',
    ];

    protected $casts = [
        'passed' => 'boolean',
        'video_watched_at' => 'datetime',
        'completed_at' => 'datetime',
        'certificate_generated_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function application()
    {
        return $this->belongsTo(MembershipApplication::class, 'membership_application_id');
    }

    public function orientation()
    {
        return $this->belongsTo(Orientation::class);
    }

    public function answers()
    {
        return $this->hasMany(OrientationAnswer::class, 'assessment_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function hasCertificate(): bool
    {
        return ! is_null($this->certificate_path);
    }

    public function isComplete(): bool
    {
        return ! is_null($this->completed_at);
    }
}
