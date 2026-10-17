<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookChunk extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_id',
        'chapter_id',
        'page_start',
        'page_end',
        'ordinal',
        'text',
        'token_count',
        'content_hash',
        'embedding',
        'embedding_model',
    ];

    protected $casts = [
        'book_id'     => 'integer',
        'chapter_id'  => 'integer',
        'page_start'  => 'integer',
        'page_end'    => 'integer',
        'ordinal'     => 'integer',
        'token_count' => 'integer',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function chapter(): BelongsTo
    {
        return $this->belongsTo(BookChapter::class, 'chapter_id');
    }

    /**
     * Float massivni L2 bo'yicha normallashtiradi (uzunligi = 1.0).
     *
     * @param  float[]  $vector
     * @return float[]
     */
    public static function normalizeVector(array $vector): array
    {
        if (empty($vector)) {
            return [];
        }

        $sumSq = 0.0;
        foreach ($vector as $val) {
            $f = (float) $val;
            $sumSq += $f * $f;
        }

        $norm = sqrt($sumSq);
        if ($norm <= 1e-12) {
            return array_map('floatval', array_values($vector));
        }

        $normalized = [];
        foreach ($vector as $val) {
            $normalized[] = ((float) $val) / $norm;
        }

        return $normalized;
    }

    /**
     * Float massivni normallashtirib, little-endian IEEE 754 float32 LONGBLOB ga o'giradi.
     *
     * @param  float[]  $vector
     */
    public static function packEmbedding(array $vector): string
    {
        $normalized = self::normalizeVector($vector);
        if (empty($normalized)) {
            return '';
        }

        return pack('g*', ...$normalized);
    }

    /**
     * LONGBLOB float32 ma'lumotini PHP float[] massiviga qaytaradi.
     *
     * @return float[]
     */
    public static function unpackEmbedding(?string $blob): array
    {
        if ($blob === null || $blob === '') {
            return [];
        }

        $unpacked = @unpack('g*', $blob);
        if (!is_array($unpacked) || empty($unpacked)) {
            return [];
        }

        return array_values($unpacked);
    }

    /**
     * Manba yorlig'ini ("45-bet", "12-13-betlar" yoki "3-bob") hosil qiladi.
     */
    public function getSourceLabelAttribute(): string
    {
        if ($this->page_start && $this->page_end && $this->page_start !== $this->page_end) {
            return "{$this->page_start}–{$this->page_end}-betlar";
        }
        if ($this->page_start) {
            return "{$this->page_start}-bet";
        }
        if ($this->chapter_id && $this->relationLoaded('chapter') && $this->chapter) {
            return $this->chapter->chapter_number . '-bob: ' . $this->chapter->title;
        }
        if ($this->chapter_id) {
            return "{$this->ordinal}-bo'lak";
        }

        return ($this->ordinal + 1) . "-bo'lak";
    }
}
