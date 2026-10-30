<?php

namespace App\Http\Livewire\Book;

use App\Http\Livewire\Concerns\LocksProperties;
use App\Http\Livewire\Concerns\WithToast;
use App\Models\Book;
use App\Models\BookComment;
use App\Models\BookCommentLike;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;

class BookDiscussionChat extends Component
{
    use WithToast;
    use LocksProperties;

    protected array $lockedProperties = ['bookId'];

    public int $bookId;
    public string $message = '';
    public $selectedPage = null;
    public ?int $replyToId = null;
    public ?string $replyToUserName = null;
    public ?string $replyToSnippet = null;
    public $rating = null;
    public string $filterPage = 'all';
    public bool $isDrawerMode = false;

    protected $listeners = [
        'book:page-changed' => 'onPageChanged',
    ];

    protected $rules = [
        'message' => 'required|string|min:1|max:2000',
    ];

    protected $messages = [
        'message.required' => 'Fikringiz yoki izohingizni yozing.',
        'message.max'      => 'Xabar 2000 ta belgidan oshmasligi kerak.',
    ];

    public function mount(Book $book, ?int $initialPage = null, bool $isDrawerMode = false): void
    {
        $this->bookId = $book->id;
        $this->selectedPage = ($initialPage && (int) $initialPage > 0) ? (int) $initialPage : null;
        $this->isDrawerMode = $isDrawerMode;
    }

    public function updatedSelectedPage($value): void
    {
        if ($value === '' || $value === 'null' || $value === null) {
            $this->selectedPage = null;
        } else {
            $this->selectedPage = (int) $value;
        }
    }

    public function updatedRating($value): void
    {
        if ($value === '' || $value === 'null' || $value === null) {
            $this->rating = null;
        } else {
            $this->rating = (int) $value;
        }
    }

    public function onPageChanged($page): void
    {
        if (!$this->isDrawerMode) {
            return;
        }

        $pageNum = (int) $page;
        if ($pageNum > 0) {
            $this->selectedPage = $pageNum;
        } else {
            $this->selectedPage = null;
        }
    }

    public function getBookProperty(): ?Book
    {
        return Book::find($this->bookId);
    }

    public function setReplyTo(int $commentId): void
    {
        $comment = BookComment::with('user')
            ->where('book_id', $this->bookId)
            ->find($commentId);
        if ($comment) {
            $this->replyToId = $comment->id;
            $this->replyToUserName = $comment->user->name ?? 'Kitobxon';
            $this->replyToSnippet = Str::limit($comment->comment, 60);
        } else {
            $this->cancelReply();
        }
    }

    public function cancelReply(): void
    {
        $this->replyToId = null;
        $this->replyToUserName = null;
        $this->replyToSnippet = null;
    }

    public function sendMessage(): void
    {
        if (!Auth::check()) {
            $this->toast('error', 'Izoh yozish va suhbatlashish uchun tizimga kiring.');
            return;
        }

        if (Auth::user()->isBanned()) {
            $this->toast('error', 'Siz bloklangansiz! Qolgan vaqt: ' . Auth::user()->ban_remaining);
            return;
        }

        $this->validate();

        $book = $this->book;
        if (!$book) {
            return;
        }

        // parent_id aynan shu kitobga tegishli ekani qat'iy tekshiriladi
        $parentId = null;
        if ($this->replyToId) {
            $parentComment = BookComment::where('id', $this->replyToId)
                ->where('book_id', $book->id)
                ->first();
            if ($parentComment) {
                $parentId = $parentComment->id;
            }
        }

        $pageNumber = null;
        if (!empty($this->selectedPage) && is_numeric($this->selectedPage) && (int) $this->selectedPage > 0) {
            $pageNumber = (int) $this->selectedPage;
        }

        $ratingValue = null;
        if (empty($parentId) && !empty($this->rating) && is_numeric($this->rating)) {
            $ratingValue = max(1, min(5, (int) $this->rating));
        }

        BookComment::create([
            'book_id'     => $book->id,
            'user_id'     => Auth::id(),
            'parent_id'   => $parentId,
            'page_number' => $pageNumber,
            'rating'      => $ratingValue,
            'comment'     => trim($this->message),
        ]);

        $this->reset(['message', 'replyToId', 'replyToUserName', 'replyToSnippet', 'rating']);
        $this->selectedPage = null;
        $this->dispatchBrowserEvent('book-chat-scroll-bottom');
        $this->toast('success', 'Fikringiz muvaffaqiyatli yuborildi!');
    }

    public function toggleLike(int $commentId): void
    {
        if (!Auth::check()) {
            $this->toast('error', 'Layk bosish uchun tizimga kiring.');
            return;
        }

        $comment = BookComment::find($commentId);
        if (!$comment) {
            return;
        }

        $userId = Auth::id();
        $like = BookCommentLike::where('book_comment_id', $commentId)
            ->where('user_id', $userId)
            ->first();

        if ($like) {
            $like->delete();
            $comment->decrement('likes_count');
        } else {
            BookCommentLike::create([
                'book_comment_id' => $commentId,
                'user_id'         => $userId,
            ]);
            $comment->increment('likes_count');
        }
    }

    public function deleteComment(int $commentId): void
    {
        if (!Auth::check()) {
            return;
        }

        $comment = BookComment::find($commentId);
        if (!$comment || !$comment->canDelete(Auth::user())) {
            $this->toast('error', 'Bu izohni o\'chirish huquqingiz yo\'q.');
            return;
        }

        $comment->delete();
        $this->toast('info', 'Izoh o\'chirildi.');
    }

    public function render()
    {
        $book = $this->book;
        $commentsQuery = BookComment::where('book_id', $this->bookId)
            ->whereNull('parent_id')
            ->with(['user', 'replies.user', 'replies.likes', 'likes'])
            ->latest();

        if ($this->filterPage !== 'all') {
            if ($this->filterPage === 'general' || $this->filterPage === '') {
                $commentsQuery->whereNull('page_number');
            } else {
                $commentsQuery->where('page_number', (int) $this->filterPage);
            }
        }

        $comments = $commentsQuery->take(50)->get();
        $totalCommentsCount = BookComment::where('book_id', $this->bookId)->count();
        $averageRating = $book ? $book->averageRating() : 0;

        return view('livewire.book.book-discussion-chat', [
            'book'               => $book,
            'comments'           => $comments,
            'totalCommentsCount' => $totalCommentsCount,
            'averageRating'      => $averageRating,
        ]);
    }
}
