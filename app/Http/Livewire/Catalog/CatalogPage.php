<?php

namespace App\Http\Livewire\Catalog;

use App\Models\Book;
use App\Services\BookRecommendationService;
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
    public string $readingStatus = 'all'; // 'all', 'reading', 'finished', 'saved'
    public string $sortBy = 'week_desc'; // 'week_desc', 'week_asc', 'popular', 'title_asc', 'chapters_desc'
    public string $mood = ''; // '', 'tezkor', 'chuqur', 'ilhomlantiruvchi', 'dam_oluvchi'

    protected $queryString = [
        'genre'         => ['except' => ''],
        'search'        => ['except' => ''],
        'format'        => ['except' => 'all'],
        'readingStatus' => ['except' => 'all'],
        'sortBy'        => ['except' => 'week_desc'],
        'mood'          => ['except' => ''],
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

    public function updatedMood(): void
    {
        $this->resetPage();
    }

    public function setMood(string $mood): void
    {
        $allowed = ['', 'tezkor', 'chuqur', 'ilhomlantiruvchi', 'dam_oluvchi'];
        $this->mood = in_array($mood, $allowed) ? $mood : '';
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
        $this->mood = '';
        $this->resetPage();
    }

    public function render()
    {
        $user = Auth::user();
        $userId = Auth::id();

        // Hisoblagichlar (Top 5 erta kirish bilan)
        $totalBooksCount = Book::availableForUser($user)->count();
        $pdfBooksCount = Book::availableForUser($user)->whereNotNull('pdf_path')->where('pdf_path', '!=', '')->count();
        $audioBooksCount = Book::availableForUser($user)->whereHas('audios')->count();
        $videoBooksCount = Book::availableForUser($user)->whereHas('videos')->count();
        $quizBooksCount = Book::availableForUser($user)->whereHas('quizzes')->count();

        $readingCount = $userId ? Book::availableForUser($user)->whereHas('readingProgress', fn ($rp) => $rp->where('user_id', $userId)->where('percent_complete', '<', 90))->count() : 0;
        $finishedCount = $userId ? Book::availableForUser($user)->whereHas('readingProgress', fn ($rp) => $rp->where('user_id', $userId)->where('percent_complete', '>=', 90))->count() : 0;
        $savedBooksCount = $userId ? Book::availableForUser($user)->whereHas('savedByUsers', fn ($q) => $q->where('users.id', $userId))->count() : 0;

        $query = Book::availableForUser($user)
            ->withCount(['chapters', 'audios', 'videos', 'quizzes']);

        // Janr filtri
        if ($this->genre !== '') {
            $query->where('genre', $this->genre);
        }

        // Kayfiyat (Mood) filtri
        if ($this->mood !== '') {
            if (\Illuminate\Support\Facades\Schema::hasColumn('books', 'mood')) {
                $query->where('mood', $this->mood);
            }
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
            } elseif ($this->readingStatus === 'saved') {
                $query->whereHas('savedByUsers', fn ($q) => $q->where('users.id', $userId));
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
            $this->mood !== '' ||
            $this->sortBy !== 'week_desc'
        );

        $recommendationService = app(BookRecommendationService::class);
        $canConsiderRecommendations = $userId !== null
            && $this->search === ''
            && $this->genre === ''
            && $this->format === 'all'
            && $this->readingStatus === 'all'
            && $this->mood === ''
            && ($totalBooksCount > 4 || $recommendationService->hasHistory($user));

        $recommendations = $canConsiderRecommendations
            ? $recommendationService->forUser($user, 4)
            : collect();

        // Tavsiyalar faqatgina ular barcha kitoblarning kichik qismi bo'lgandagina (subset) ko'rsatiladi.
        // Agar tavsiyalar barcha kitoblarni qamrab olsa, kitoblar 2 marta takrorlanmasligi uchun
        // alohida tavsiya bo'limi ko'rsatilmaydi, to'g'ridan-to'g'ri asosiy katalogda 1 marta chiqadi.
        $showRecommendations = $recommendations->isNotEmpty() && $totalBooksCount > $recommendations->count();

        if ($showRecommendations) {
            $query->whereNotIn('id', $recommendations->pluck('id'));
        }

        $books = $query->paginate(12);

        return view('livewire.catalog.catalog-page', [
            'books'             => $books,
            'recommendations'   => $recommendations,
            'showRecommendations' => $showRecommendations,
            'genres'            => $genres,
            'totalBooksCount'   => $totalBooksCount,
            'pdfBooksCount'     => $pdfBooksCount,
            'audioBooksCount'   => $audioBooksCount,
            'videoBooksCount'   => $videoBooksCount,
            'quizBooksCount'    => $quizBooksCount,
            'readingCount'      => $readingCount,
            'finishedCount'     => $finishedCount,
            'savedBooksCount'   => $savedBooksCount,
            'hasActiveFilters'  => $hasActiveFilters,
            'mood'              => $this->mood,
        ])->layout('layouts.app', ['title' => 'Kitoblar katalogi']);
    }
}
