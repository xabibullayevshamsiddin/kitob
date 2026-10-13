<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PdfAnnotation extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'page_number',
        'kind',
        'label',
        'note_text',
        'color',
    ];

    protected $casts = [
        'page_number' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
