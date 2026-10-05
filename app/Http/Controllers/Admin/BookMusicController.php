<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookMusic;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookMusicController extends Controller
{
    /**
     * Kitobning barcha fon musiqalari ro'yxati.
     */
    public function index(Book $book)
    {
        $musics = $book->musics()->get();

        return view('admin.books.music.index', compact('book', 'musics'));
    }

    /**
     * Yangi fon musiqasini yuklash yoki tayyor presetdan qo'shish.
     */
    public function store(Request $request, Book $book)
    {
        $request->validate([
            'title'        => 'required|string|max:255',
            'music_file'   => 'nullable|file|mimes:mp3,wav,ogg,m4a,aac,webm|max:51200',
            'preset_track' => 'nullable|string|in:rain,piano,forest',
            'order'        => 'nullable|integer|min:1|max:999',
        ], [
            'title.required'    => 'Musiqa sarlavhasi / nomi kiritilishi shart.',
            'music_file.mimes'  => 'Faqat audio formatdagi fayllarni (.mp3, .wav, .ogg, .m4a, .aac) yuklash mumkin.',
            'music_file.max'    => 'Audio fayl hajmi 50 MB dan oshmasligi kerak.',
        ]);

        if (!$request->hasFile('music_file') && !$request->filled('preset_track')) {
            return back()->withErrors(['music_file' => 'Iltimos, audio fayl yuklang yoki tayyor ambient ohanglardan birini tanlang.'])->withInput();
        }

        $filePath = null;

        if ($request->hasFile('music_file')) {
            $filePath = $request->file('music_file')->store('books/music', 'public');
        } elseif ($request->filled('preset_track')) {
            $preset = $request->preset_track;
            $presetSource = 'books/music/presets/' . $preset . '.wav';

            if (Storage::disk('public')->exists($presetSource)) {
                $targetPath = 'books/music/' . $book->id . '_' . $preset . '_' . time() . '.wav';
                Storage::disk('public')->copy($presetSource, $targetPath);
                $filePath = $targetPath;
            } else {
                $filePath = 'sounds/ambient/' . $preset . '.wav';
            }
        }

        $nextOrder = $request->filled('order')
            ? (int) $request->order
            : ((int) BookMusic::where('book_id', $book->id)->max('order') + 1);

        BookMusic::create([
            'book_id'   => $book->id,
            'title'     => $request->title,
            'file_path' => $filePath,
            'order'     => $nextOrder,
            'is_active' => true,
        ]);

        return back()->with('success', "«{$book->title}» kitobiga yangi fon musiqasi muvaffaqiyatli ulandi! 🎵");
    }

    /**
     * Musiqani faol yoki nofaol qilish (Toggle).
     */
    public function toggle(Book $book, BookMusic $music)
    {
        abort_unless($music->book_id === $book->id, 403);

        $music->update([
            'is_active' => !$music->is_active,
        ]);

        $statusText = $music->is_active ? 'faollashtirildi' : 'nofaol qilindi';

        return back()->with('success', "«{$music->title}» musiqasi {$statusText}.");
    }

    /**
     * Fon musiqasini o'chirish.
     */
    public function destroy(Book $book, BookMusic $music)
    {
        abort_unless($music->book_id === $book->id, 403);

        // Faylni storage dan o'chirish
        if ($music->file_path && Storage::disk('public')->exists($music->file_path)) {
            // Agar u umumiy presets papkasida bo'lmasa
            if (!str_contains($music->file_path, 'presets/')) {
                Storage::disk('public')->delete($music->file_path);
            }
        }

        $music->delete();

        return back()->with('success', "Fon musiqasi muvaffaqiyatli o'chirildi! 🗑️");
    }
}
