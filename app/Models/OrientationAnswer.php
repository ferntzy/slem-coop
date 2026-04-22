<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrientationAnswer extends Model
{
    use HasFactory;

    protected $fillable = [
        'assessment_id',
        'question_id',
        'choice_id',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function assessment()
    {
        return $this->belongsTo(OrientationAssessment::class, 'assessment_id');
    }

    public function question()
    {
        return $this->belongsTo(OrientationQuestion::class, 'question_id');
    }

    public function choice()
    {
        return $this->belongsTo(OrientationChoice::class, 'choice_id');
    }

    // ── Helpers ──────────────────────────────────────────────────────────────

    public function isCorrect(): bool
    {
        return $this->choice->is_correct;
    }
}
