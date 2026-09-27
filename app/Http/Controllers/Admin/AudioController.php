<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookAudio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AudioController extends Controller
{
    public function index()
    {
        $audios = BookAudio::with(['book:id,title,cover_image,slug'])
            ->latest()
            ->paginate(15);

        return view('admin.audios.index', compact('audios'));
    }

    public function create(Request $request)
    {
        $books = Book::orderBy('title')->get(['id', 'title', 'week_number', 'author']);
        $selectedBookId = $request->query('book_id');

        return view('admin.audios.create', compact('books', 'selectedBookId'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'book_id'    => 'required|exists:books,id',
            'title'      => 'required|string|max:255',
            'duration'   => 'nullable|numeric|min:0.1',
            'audio_file' => 'nullable|file|mimes:mp3,wav,ogg,m4a,aac,wma|max:102400',
            'audio_url'  => 'nullable|string|max:500',
        ], [
            'book_id.required'    => 'Kitobni tanlash majburiy.',
            'title.required'      => 'Audio dars yoki bob sarlavhasini kiriting.',
            'audio_file.max'      => 'Audio fayl hajmi 100 MB dan oshmasligi kerak.',
            'audio_file.mimes'    => 'Faqat MP3, WAV, M4A, OGG formatidagi audio fayllarni yuklash mumkin.',
        ]);

        if (!$request->hasFile('audio_file') && empty($request->audio_url)) {
            return back()->withInput()->withErrors(['audio_file' => 'Audio faylni yuklang yoki audio havola (URL) kiriting.']);
        }

        $filePath = '';
        if ($request->hasFile('audio_file')) {
            $filePath = $request->file('audio_file')->store('books/audios', 'public');
        } else {
            $filePath = trim($request->audio_url);
        }

        $durationSeconds = $request->duration ? (int) round($request->duration * 60) : null;

        BookAudio::create([
            'book_id'   => $request->book_id,
            'title'     => trim($request->title),
            'file_path' => $filePath,
            'duration'  => $durationSeconds,
        ]);

        return redirect()->route('admin.audios.index')->with('success', 'Audio muvaffaqiyatli yuklandi va kitobga biriktirildi! 🎵');
    }

    public function destroy(BookAudio $audio)
    {
        if ($audio->file_path && !str_starts_with($audio->file_path, 'http')) {
            Storage::disk('public')->delete($audio->file_path);
        }

        $audio->delete();

        return redirect()->route('admin.audios.index')->with('success', 'Audio muvaffaqiyatli o\'chirildi.');
    }
}
