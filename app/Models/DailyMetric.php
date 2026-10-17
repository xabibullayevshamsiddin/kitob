<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DailyMetric extends Model
{
    use HasFactory;

    protected $fillable = [
        'date',
        'dau',
        'logged_in_users',
        'reading_users',
        'active_readers',
        'new_users',
        'onboarded',
        'minutes_read',
        'books_finished',
        'quiz_attempts',
        'points_issued',
        'coins_issued',
        'chat_messages',
        'ai_messages',
        'ai_tokens',
    ];

    protected $casts = [
        'date'            => 'date:Y-m-d',
        'dau'             => 'integer',
        'logged_in_users' => 'integer',
        'reading_users'   => 'integer',
        'active_readers'  => 'integer',
        'new_users'       => 'integer',
        'onboarded'       => 'integer',
        'minutes_read'    => 'integer',
        'books_finished'  => 'integer',
        'quiz_attempts'   => 'integer',
        'points_issued'   => 'integer',
        'coins_issued'    => 'integer',
        'chat_messages'   => 'integer',
        'ai_messages'     => 'integer',
        'ai_tokens'       => 'integer',
    ];

    /**
     * SQLite va MySQL uchun date ustunini doim 'Y-m-d' formatida saqlash
     */
    public function fromDateTime($value)
    {
        return empty($value) ? $value : $this->asDateTime($value)->format('Y-m-d');
    }
}
