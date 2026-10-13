<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\PdfAnnotation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class PdfAnnotationController extends Controller
{
    public function store(Request $request, Book $book): JsonResponse
    {
        abort_unless($book->pdf_path && Storage::disk('public')->exists($book->pdf_path), 404);

        $data = $request->validate([
            'page_number' => ['required', 'integer', 'min:1', 'max:100000'],
            'kind' => ['required', 'string', 'in:bookmark,note'],
            'label' => ['nullable', 'string', 'max:120'],
            'note_text' => ['nullable', 'string', 'max:5000', 'required_if:kind,note'],
            'color' => ['nullable', 'string', 'in:amber,emerald,sky,rose,purple'],
        ]);

        $annotation = PdfAnnotation::create([
            'user_id' => $request->user()->id,
            'book_id' => $book->id,
            'page_number' => $data['page_number'],
            'kind' => $data['kind'],
            'label' => trim((string) ($data['label'] ?? '')) ?: null,
            'note_text' => trim((string) ($data['note_text'] ?? '')) ?: null,
            'color' => $data['color'] ?? 'amber',
        ]);

        return response()->json([
            'annotation' => [
                'id' => $annotation->id,
                'page_number' => $annotation->page_number,
                'kind' => $annotation->kind,
                'label' => $annotation->label,
                'note_text' => $annotation->note_text,
                'color' => $annotation->color,
                'created_at' => $annotation->created_at->format('d.m.Y H:i'),
            ],
        ], 201);
    }

    public function destroy(Request $request, PdfAnnotation $pdfAnnotation): JsonResponse
    {
        abort_unless((int) $pdfAnnotation->user_id === (int) $request->user()->id, 404);

        $pdfAnnotation->delete();

        return response()->json(['deleted' => true]);
    }
}
