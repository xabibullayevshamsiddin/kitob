<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class BookPdfController extends Controller
{
    /**
     * Kitobni PDF sifatida yuklab beradi.
     * Agar admin/teacher haqiqiy PDF fayl yuklagan bo'lsa — aynan shu fayl beriladi,
     * aks holda kitob boblaridan DomPDF orqali chiroyli PDF yaratiladi.
     */
    public function download(Book $book)
    {
        // 1) Yuklangan haqiqiy PDF fayl bormi?
        if ($book->pdf_path && Storage::disk('public')->exists($book->pdf_path)) {
            $absolute = Storage::disk('public')->path($book->pdf_path);
            $filename = Str::slug($book->title) . '.pdf';

            return response()->download($absolute, $filename);
        }

        // 2) Yo'q bo'lsa — boblardan PDF yaratamiz
        $chapters = $book->chapters()
            ->where('is_published', true)
            ->orderBy('chapter_number')
            ->get();

        abort_if($chapters->isEmpty(), 404, "Bu kitobda hali matn boblar yo'q va PDF fayl yuklanmagan.");

        $pdf = Pdf::loadView('pdf.book', [
                'book'     => $book,
                'chapters' => $chapters,
            ])
            ->setPaper('a4')
            ->setOptions([
                'isRemoteEnabled' => true,   // masofaviy rasm (muqova) yuklash uchun
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        $filename = Str::slug($book->title) . '.pdf';

        return $pdf->download($filename);
    }
}
