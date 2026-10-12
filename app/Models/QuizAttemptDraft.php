<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class QuizAttemptDraft extends Model
{
    protected $fillable = [
        'user_id',
        'quiz_id',
        'current_question',
        'answers',
        'started_at',
    ];

    protected $casts = [
        'current_question' => 'integer',
        'answers' => 'array',
        'started_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }
}
