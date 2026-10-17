<?php

namespace App\Observers;

use App\Models\Book;
use App\Models\BookChapter;

class BookChapterObserver
{
    public function created(BookChapter $chapter): void
    {
        $this->markBookIndexStale($chapter);
    }

    public function updated(BookChapter $chapter): void
    {
        if ($chapter->wasChanged(['title', 'content', 'chapter_number', 'is_published'])) {
            $this->markBookIndexStale($chapter);
        }
    }

    public function deleted(BookChapter $chapter): void
    {
        $this->markBookIndexStale($chapter);
    }

    protected function markBookIndexStale(BookChapter $chapter): void
    {
        if (!$chapter->book_id) {
            return;
        }

        Book::where('id', $chapter->book_id)
            ->where('ai_index_status', 'ready')
            ->update(['ai_index_status' => 'stale']);
    }
}
