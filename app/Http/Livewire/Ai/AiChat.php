<?php

namespace App\Http\Livewire\Ai;

use App\Models\AiChatHistory;
use App\Services\AI\AiChatService;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AiChat extends Component
{
    public string $query = '';

    public bool $loading = false;

    /** Chat history rows (messages + responses merged, oldest first) */
    public array $history = [];

    public function mount(): void
    {
        $this->loadHistory();
    }

    protected function loadHistory(): void
    {
        $rows = AiChatHistory::where('user_id', Auth::id())
            ->latest()
            ->take(20)
            ->get()
            ->reverse()
            ->values();

        $merged = [];
        foreach ($rows as $row) {
            $merged[] = ['role' => 'user', 'text' => $row->message];
            if ($row->response) {
                $merged[] = ['role' => 'assistant', 'text' => $row->response];
            }
        }

        $this->history = $merged;
    }

    public function ask(): void
    {
        $this->validate([
            'query' => 'required|string|min:2|max:500',
        ], [
            'query.required' => 'Savol yozing.',
            'query.max'      => 'Savol 500 belgidan oshmasligi kerak.',
        ]);

        $user     = Auth::user();
        $question = trim($this->query);

        // Featured book context
        $featuredBook = \App\Models\Book::where('is_active', true)
            ->orderBy('week_number', 'desc')
            ->first();

        $service = app(AiChatService::class);

        // Show the user's message immediately
        $this->history[] = ['role' => 'user', 'text' => $question];

        $result = $service->ask($question, [
            'book_title' => $featuredBook?->title ?? 'Platforma kitoblari',
        ]);

        $service->saveHistory(
            $user,
            $featuredBook,
            $question,
            $result['response'],
            (int) $result['tokens_used'] ?? 0
        );

        $this->history[] = ['role' => 'assistant', 'text' => $result['response']];

        $this->reset('query', 'loading');

        $this->dispatchBrowserEvent('ai-scroll-bottom');
    }

    public function render()
    {
        return view('livewire.ai.ai-chat')
            ->layout('layouts.app', ['title' => 'AI Maslahatchi']);
    }
}
