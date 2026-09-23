<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttempt extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'quiz_id',
        'score',
        'max_score',
        'percent',
        'answers',
        'is_full_points_awarded',
        'completed_at',
    ];

    protected $casts = [
        'answers'               => 'array',
        'is_full_points_awarded'=> 'boolean',
        'completed_at'          => 'datetime',
        'percent'               => 'decimal:2',
        'score'                 => 'integer',
        'max_score'             => 'integer',
    ];

    // -------------------------------------------------------------------------
    // Relationships
    // -------------------------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    // -------------------------------------------------------------------------
    // Scopes
    // -------------------------------------------------------------------------

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    // -------------------------------------------------------------------------
    // Accessors
    // -------------------------------------------------------------------------

    /**
     * Returns a letter grade based on the attempt percentage.
     * A ≥ 90 | B ≥ 75 | C ≥ 60 | D ≥ 50 | F < 50
     */
    public function getGradeAttribute(): string
    {
        $pct = (float) $this->percent;

        return match (true) {
            $pct >= 90 => 'A',
            $pct >= 75 => 'B',
            $pct >= 60 => 'C',
            $pct >= 50 => 'D',
            default    => 'F',
        };
    }
}
