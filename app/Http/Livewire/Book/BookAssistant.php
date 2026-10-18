<?php

namespace App\Http\Livewire\Book;

use App\Http\Livewire\Concerns\WithToast;
use App\Models\AiChatHistory;
use App\Models\Book;
use App\Models\BookChapter;
use App\Services\AI\BookAssistant\BookAssistant as BookAssistantService;
use Carbon\Carbon;
use Livewire\Component;

class BookAssistant extends Component
{
    use WithToast;

    public int $bookId;
    public string $question = '';
    public bool $isDrawer = false;
    public ?string $errorMessage = null;
    public ?string $lastAnnouncement = null;

    public function mount(Book $book, bool $isDrawer = false): void
    {
        $this->bookId = (int) $book->id;
        $this->isDrawer = $isDrawer;
    }

    /**
     * Foydalanuvchi savolini yuboradi (yoki tayyor 3 ta savoldan birini bosganda ishlaydi).
     * Livewire 2 xavfsizligi: har chaqiruvda Book::availableForUser() qayta tekshiriladi.
     */
    public function ask(?string $presetQuestion = null): void
    {
        $this->errorMessage = null;

        if (!auth()->check()) {
            $msg = trans('ai.unauthorized');
            $this->errorMessage = $msg;
            $this->toastWarning($msg);
            return;
        }

        if ($presetQuestion !== null && trim($presetQuestion) !== '') {
            $this->question = trim($presetQuestion);
        }

        $trimmed = trim($this->question);
        if ($trimmed === '') {
            $msg = trans('ai.question_empty');
            $this->errorMessage = $msg;
            return;
        }

        // Public property ($this->bookId) brauzerdan o'zgartirilgan bo'lsa ham ruxsatni qayta tekshiramiz
        $book = Book::availableForUser(auth()->user())->find($this->bookId);
        if (!$book || !$book->is_active || !$book->ai_enabled || !in_array($book->ai_index_status, ['ready', 'partial'], true)) {
            $msg = trans('ai.book_not_ready');
            $this->errorMessage = $msg;
            $this->toastError($msg);
            return;
        }

        /** @var BookAssistantService $service */
        $service = app(BookAssistantService::class);
        $result = $service->ask(auth()->user(), $book->id, $trimmed);

        if ($result['status'] === 'error') {
            $this->errorMessage = $result['answer'];
            $this->toastError($result['answer']);
            return;
        }

        $this->question = '';
        $this->lastAnnouncement = mb_strimwidth(strip_tags($result['answer']), 0, 180, '...');

        $this->dispatchBrowserEvent('book-ai-updated', [
            'bookId' => $book->id,
            'status' => $result['status'],
        ]);
    }

    /**
     * Faqat joriy foydalanuvchining shu kitob bo'yicha suhbat tarixini tozalaydi.
     */
    public function clearHistory(): void
    {
        if (!auth()->check()) {
            return;
        }

        $book = Book::availableForUser(auth()->user())->find($this->bookId);
        if (!$book) {
            return;
        }

        AiChatHistory::where('user_id', auth()->id())
            ->where('book_id', $book->id)
            ->delete();

        $this->errorMessage = null;
        $this->lastAnnouncement = trans('ai.history_cleared');
        $this->toastSuccess(trans('ai.history_cleared'));
    }

    /**
     * 3 ta tayyor savol ("Bu kitob nima haqida?", "Asosiy g'oyalar nimalar?", va bob/bet savoli).
     *
     * @return array<int, string>
     */
    public function getSuggestedQuestionsProperty(): array
    {
        $firstChapter = BookChapter::where('book_id', $this->bookId)
            ->orderBy('chapter_number')
            ->first(['chapter_number', 'title']);

        if ($firstChapter && trim((string) $firstChapter->title) !== '') {
            $thirdQuestion = sprintf('«%s» bobini tushuntirib ber', trim($firstChapter->title));
        } else {
            $thirdQuestion = '1-betdagi asosiy tushunchalarni tushuntirib ber';
        }

        return [
            'Bu kitob nima haqida?',
            'Asosiy g\'oyalar nimalar?',
            $thirdQuestion,
        ];
    }

    public function render()
    {
        $book = Book::find($this->bookId);
        /** @var BookAssistantService $service */
        $service = app(BookAssistantService::class);

        $messages = collect();
        $user = auth()->user();
        $isAdmin = $user && method_exists($user, 'isAdmin') && $user->isAdmin();
        $dailyLimit = $isAdmin
            ? (int) config('ai.daily_limit_per_admin', 50)
            : (int) config('ai.daily_limit_per_user', 10);
        $remainingToday = $dailyLimit;
        $resetRemainingHuman = '';

        if ($user && $book) {
            $historyRows = AiChatHistory::where('user_id', $user->id)
                ->where('book_id', $book->id)
                ->orderBy('id')
                ->limit(40)
                ->get();

            $messages = $historyRows->map(function (AiChatHistory $row) use ($service) {
                return [
                    'id'             => $row->id,
                    'question'       => $row->message,
                    'answer_html'    => $service->renderSafeMarkdown((string) $row->response),
                    'sources'        => is_array($row->sources) ? $row->sources : [],
                    'refused'        => (bool) $row->refused,
                    'refusal_reason' => $row->refusal_reason,
                    'created_at'     => $row->created_at ? $row->created_at->format('H:i') : '',
                ];
            });

            $nowTashkent = Carbon::now('Asia/Tashkent');
            $todayStart = $nowTashkent->copy()->startOfDay()->utc();
            $usedToday = AiChatHistory::where('user_id', $user->id)
                ->where('created_at', '>=', $todayStart)
                ->count();

            $remainingToday = max(0, $dailyLimit - $usedToday);

            $resetAt = $nowTashkent->copy()->endOfDay();
            $diffHours = (int) $nowTashkent->diffInHours($resetAt);
            $diffMinutes = (int) ($nowTashkent->diffInMinutes($resetAt) % 60);

            if ($diffHours > 0) {
                $resetRemainingHuman = $diffMinutes > 0
                    ? "{$diffHours} soat {$diffMinutes} daqiqa"
                    : "{$diffHours} soat";
            } else {
                $resetRemainingHuman = max(1, $diffMinutes) . " daqiqa";
            }
        }

        return view('livewire.book.book-assistant', [
            'book'                => $book,
            'messages'            => $messages,
            'suggestedQuestions'  => $this->suggestedQuestions,
            'remainingToday'      => $remainingToday,
            'dailyLimit'          => $dailyLimit,
            'resetRemainingHuman' => $resetRemainingHuman,
            'maxChars'            => (int) config('ai.max_question_chars', 500),
        ]);
    }
}
