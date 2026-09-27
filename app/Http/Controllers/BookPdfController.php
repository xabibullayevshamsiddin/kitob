<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class BookPdfController extends Controller
{
    /**
     * Kitobni BRAUZERDA onlayn o'qish (embed).
     * Yuklangan PDF bormi — o'sha; yo'q bo'lsa boblardan PDF generatsiya qilib, inline ko'rsatiladi.
     */
    public function read(Book $book)
    {
        // 1) Yuklangan haqiqiy PDF fayl bormi?
        if ($book->pdf_path && Storage::disk('public')->exists($book->pdf_path)) {
            $absolute = Storage::disk('public')->path($book->pdf_path);

            return response()->file($absolute, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . Str::slug($book->title) . '.pdf"',
                'X-Content-Type-Options' => 'nosniff',
            ]);
        }

        // 2) Yo'q bo'lsa — boblardan PDF yaratib, inline ko'rsatamiz
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
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
            ]);

        $filename = Str::slug($book->title) . '.pdf';

        return response($pdf->output(), 200, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

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

    /**
     * PDF faylni brauzerda / PDF.js da "inline" oqim (stream) qilib berish.
     * HTTP Range so'rovlarini qo'llab-quvvatlaydi.
     */
    public function stream(Book $book)
    {
        abort_unless($book->pdf_path && Storage::disk('public')->exists($book->pdf_path), 404);
        $path = Storage::disk('public')->path($book->pdf_path);

        return response()->file($path, [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . Str::slug($book->title) . '.pdf"',
        ]);
    }
}
