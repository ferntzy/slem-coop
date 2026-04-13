<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrientationQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'orientation_id',
        'question',
        'points',
        'order',
    ];

    // ── Relationships ────────────────────────────────────────────────────────

    public function orientation()
    {
        return $this->belongsTo(Orientation::class);
    }

    public function choices()
    {
        return $this->hasMany(OrientationChoice::class, 'question_id');
    }

    public function correctChoice()
    {
        return $this->hasOne(OrientationChoice::class, 'question_id')
                    ->where('is_correct', true);
    }
}