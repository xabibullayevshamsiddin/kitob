<?php

namespace App\Http\Livewire\Catalog;

use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class CatalogPage extends Component
{
    use WithPagination;

    protected $paginationTheme = 'tailwind';

    public string $genre = '';
    public string $search = '';
    public string $format = 'all'; // 'all', 'pdf', 'audio', 'video', 'quiz'
    public string $readingStatus = 'all'; // 'all', 'reading', 'finished'
    public string $sortBy = 'week_desc'; // 'week_desc', 'week_asc', 'popular', 'title_asc', 'chapters_desc'

    protected $queryString = [
        'genre'         => ['except' => ''],
        'search'        => ['except' => ''],
        'format'        => ['except' => 'all'],
        'readingStatus' => ['except' => 'all'],
        'sortBy'        => ['except' => 'week_desc'],
    ];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedGenre(): void
    {
        $this->resetPage();
    }

    public function updatedFormat(): void
    {
        $this->resetPage();
    }

    public function updatedReadingStatus(): void
    {
        $this->resetPage();
    }

    public function updatedSortBy(): void
    {
        $this->resetPage();
    }

    public function setFormat(string $format): void
    {
        $this->format = $format;
        $this->readingStatus = 'all';
        $this->resetPage();
    }

    public function setReadingStatus(string $status): void
    {
        $this->readingStatus = $status;
        $this->format = 'all';
        $this->resetPage();
    }

    public function setGenre(string $genre): void
    {
        $this->genre = $genre;
        $this->resetPage();
    }

    public function resetFilters(): void
    {
        $this->search = '';
        $this->genre = '';
        $this->format = 'all';
        $this->readingStatus = 'all';
        $this->sortBy = 'week_desc';
        $this->resetPage();
    }

    public function render()
    {
        $userId = Auth::id();

        // Hisoblagichlar
        $totalBooksCount = Book::where('is_active', true)->count();
        $pdfBooksCount = Book::where('is_active', true)->whereNotNull('pdf_path')->where('pdf_path', '!=', '')->count();
        $audioBooksCount = Book::where('is_active', true)->whereHas('audios')->count();
        $videoBooksCount = Book::where('is_active', true)->whereHas('videos')->count();
        $quizBooksCount = Book::where('is_active', true)->whereHas('quizzes')->count();

        $readingCount = $userId ? Book::where('is_active', true)->whereHas('readingProgress', fn ($rp) => $rp->where('user_id', $userId)->where('percent_complete', '<', 90))->count() : 0;
        $finishedCount = $userId ? Book::where('is_active', true)->whereHas('readingProgress', fn ($rp) => $rp->where('user_id', $userId)->where('percent_complete', '>=', 90))->count() : 0;

        $query = Book::query()
            ->where('is_active', true)
            ->withCount(['chapters', 'audios', 'videos', 'quizzes']);

        // Janr filtri
        if ($this->genre !== '') {
            $query->where('genre', $this->genre);
        }

        // Format filtri
        if ($this->format === 'pdf') {
            $query->whereNotNull('pdf_path')->where('pdf_path', '!=', '');
        } elseif ($this->format === 'audio') {
            $query->whereHas('audios');
        } elseif ($this->format === 'video') {
            $query->whereHas('videos');
        } elseif ($this->format === 'quiz') {
            $query->whereHas('quizzes');
        }

        // Foydalanuvchi mutolaa holati filtri
        if ($userId) {
            if ($this->readingStatus === 'reading') {
                $query->whereHas('readingProgress', fn ($rp) => $rp->where('user_id', $userId)->where('percent_complete', '<', 90));
            } elseif ($this->readingStatus === 'finished') {
                $query->whereHas('readingProgress', fn ($rp) => $rp->where('user_id', $userId)->where('percent_complete', '>=', 90));
            }
        }

        // Qidiruv
        if ($this->search !== '') {
            $s = '%' . trim($this->search) . '%';
            $query->where(function ($q) use ($s) {
                $q->where('title', 'like', $s)
                  ->orWhere('author', 'like', $s)
                  ->orWhere('description', 'like', $s);
            });
        }

        // Saralash
        if ($this->sortBy === 'week_asc') {
            $query->orderBy('week_number', 'asc');
        } elseif ($this->sortBy === 'popular') {
            $query->withCount('readingSessions')->orderByDesc('reading_sessions_count');
        } elseif ($this->sortBy === 'title_asc') {
            $query->orderBy('title', 'asc');
        } elseif ($this->sortBy === 'chapters_desc') {
            $query->orderByDesc('chapters_count');
        } else {
            // Default: haftalar bo'yicha eng yangisi birinchi
            $query->orderBy('week_number', 'desc');
        }

        $books = $query->paginate(12);

        $genres = Book::query()
            ->where('is_active', true)
            ->select('genre')
            ->distinct()
            ->pluck('genre')
            ->filter()
            ->values();

        $hasActiveFilters = (
            $this->search !== '' ||
            $this->genre !== '' ||
            $this->format !== 'all' ||
            $this->readingStatus !== 'all' ||
            $this->sortBy !== 'week_desc'
        );

        return view('livewire.catalog.catalog-page', [
            'books'             => $books,
            'genres'            => $genres,
            'totalBooksCount'   => $totalBooksCount,
            'pdfBooksCount'     => $pdfBooksCount,
            'audioBooksCount'   => $audioBooksCount,
            'videoBooksCount'   => $videoBooksCount,
            'quizBooksCount'    => $quizBooksCount,
            'readingCount'      => $readingCount,
            'finishedCount'     => $finishedCount,
            'hasActiveFilters'  => $hasActiveFilters,
        ])->layout('layouts.app', ['title' => 'Kitoblar katalogi']);
    }
}
