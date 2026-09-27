<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookVideo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function index()
    {
        $videos = BookVideo::with(['book:id,title,cover_image,slug'])
            ->latest()
            ->paginate(15);

        return view('admin.videos.index', compact('videos'));
    }

    public function create(Request $request)
    {
        $books = Book::orderBy('title')->get(['id', 'title', 'week_number', 'author']);
        $selectedBookId = $request->query('book_id');

        return view('admin.videos.create', compact('books', 'selectedBookId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'book_id'        => 'required|exists:books,id',
            'title'          => 'required|string|max:255',
            'type'           => 'required|in:overview,chapter',
            'chapter_number' => 'nullable|integer|min:1',
            'duration'       => 'nullable|numeric|min:0.1',
            'video_file'     => 'nullable|file|mimes:mp4,webm,mov,avi,mkv|max:204800',
            'video_url'      => 'nullable|string|max:500',
            'thumbnail'      => 'nullable|image|mimes:jpeg,png,jpg,webp|max:10240',
        ], [
            'book_id.required'   => 'Kitobni tanlash majburiy.',
            'title.required'     => 'Video dars sarlavhasini kiriting.',
            'video_file.max'     => 'Video hajmi 200 MB dan oshmasligi kerak.',
            'video_file.mimes'   => 'Faqat MP4, WebM, MOV video formatlarini yuklash mumkin.',
            'thumbnail.max'      => 'Muqova rasmi 10 MB dan oshmasligi kerak.',
        ]);

        if (!$request->hasFile('video_file') && empty($request->video_url)) {
            return back()->withInput()->withErrors(['video_file' => 'Video faylni yuklang yoki video havola (URL / YouTube) kiriting.']);
        }

        $videoPath = '';
        if ($request->hasFile('video_file')) {
            $videoPath = $request->file('video_file')->store('books/videos', 'public');
        } else {
            $videoPath = trim($request->video_url);
        }

        $thumbPath = null;
        if ($request->hasFile('thumbnail')) {
            $thumbPath = $request->file('thumbnail')->store('books/video_thumbs', 'public');
        }

        $durationSeconds = $request->duration ? (int) round($request->duration * 60) : null;

        BookVideo::create([
            'book_id'        => $request->book_id,
            'title'          => trim($request->title),
            'type'           => $request->type,
            'chapter_number' => $request->chapter_number,
            'video_path'     => $videoPath,
            'thumbnail'      => $thumbPath,
            'duration'       => $durationSeconds,
            'is_processed'   => true,
        ]);

        return redirect()->route('admin.videos.index')->with('success', 'Video dars muvaffaqiyatli saqlandi va kitobga bog\'landi! 🎥');
    }

    public function destroy(BookVideo $video)
    {
        if ($video->video_path && !str_starts_with($video->video_path, 'http')) {
            Storage::disk('public')->delete($video->video_path);
        }
        if ($video->thumbnail && !str_starts_with($video->thumbnail, 'http')) {
            Storage::disk('public')->delete($video->thumbnail);
        }

        $video->delete();

        return redirect()->route('admin.videos.index')->with('success', 'Video muvaffaqiyatli o\'chirildi.');
    }
}
