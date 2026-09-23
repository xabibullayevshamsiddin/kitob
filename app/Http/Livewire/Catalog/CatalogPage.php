<?php

namespace App\Http\Livewire\Catalog;

use App\Models\Book;
use Livewire\Component;

class CatalogPage extends Component
{
    public string $genre = '';

    public string $search = '';

    protected $queryString = ['genre', 'search'];

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedGenre(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $books = Book::query()
            ->when($this->genre !== '', fn ($q) => $q->where('genre', $this->genre))
            ->when($this->search !== '', function ($q) {
                $s = '%' . $this->search . '%';
                $q->where(fn ($w) => $w->where('title', 'like', $s)
                    ->orWhere('author', 'like', $s)
                    ->orWhere('description', 'like', $s));
            })
            ->orderBy('week_number', 'desc')
            ->withCount('chapters')
            ->get();

        $genres = Book::query()
            ->select('genre')
            ->distinct()
            ->pluck('genre')
            ->filter()
            ->values();

        return view('livewire.catalog.catalog-page', [
            'books'  => $books,
            'genres' => $genres,
        ])->layout('layouts.app', ['title' => 'Kitoblar katalogi']);
    }
}
