<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrientationChoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'question_id',
        'choice_text',
        'is_correct',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function question()
    {
        return $this->belongsTo(OrientationQuestion::class, 'question_id');
    }
}
