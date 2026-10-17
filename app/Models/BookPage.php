<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookPage extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'page_number',
        'text',
        'source',
        'quality_score',
    ];

    protected $casts = [
        'book_id'       => 'integer',
        'page_number'   => 'integer',
        'quality_score' => 'float',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
}
