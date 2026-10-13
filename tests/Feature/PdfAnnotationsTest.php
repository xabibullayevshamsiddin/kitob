<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\PdfAnnotation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PdfAnnotationsTest extends TestCase
{
    use RefreshDatabase;

    private function makePdfBook(): array
    {
        Storage::fake('public');
        $pdfPath = 'books/pdf/annotations-test.pdf';
        Storage::disk('public')->put($pdfPath, '%PDF-1.4 test fixture');

        $user = User::create([
            'name' => 'PDF Reader',
            'username' => 'pdf_reader_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);

        $book = Book::create([
            'title' => 'PDF qaydlar kitobi',
            'slug' => 'pdf-qaydlar-' . uniqid(),
            'author' => 'Muallif',
            'description' => 'PDF test kitobi',
            'genre' => 'Adabiyot',
            'week_number' => 1,
            'is_active' => true,
            'pdf_path' => $pdfPath,
        ]);

        return [$user, $book];
    }

    public function test_user_can_save_a_private_bookmark_and_note_on_a_pdf_page(): void
    {
        [$user, $book] = $this->makePdfBook();

        $this->actingAs($user)
            ->postJson(route('books.pdf.annotations.store', $book), [
                'page_number' => 17,
                'kind' => 'bookmark',
                'label' => 'Muhim joy',
            ])
            ->assertCreated()
            ->assertJsonPath('annotation.page_number', 17)
            ->assertJsonPath('annotation.kind', 'bookmark');

        $this->actingAs($user)
            ->postJson(route('books.pdf.annotations.store', $book), [
                'page_number' => 17,
                'kind' => 'note',
                'label' => 'Qahramon haqida',
                'note_text' => 'Bu betda muhim voqea yuz beradi.',
            ])
            ->assertCreated()
            ->assertJsonPath('annotation.page_number', 17)
            ->assertJsonPath('annotation.note_text', 'Bu betda muhim voqea yuz beradi.');

        $this->assertDatabaseHas('pdf_annotations', [
            'user_id' => $user->id,
            'book_id' => $book->id,
            'page_number' => 17,
            'kind' => 'note',
            'note_text' => 'Bu betda muhim voqea yuz beradi.',
        ]);

        $this->get(route('books.flipbook', $book))
            ->assertOk()
            ->assertSee('PDF bet belgilari')
            ->assertSee('Muhim joy')
            ->assertSee('Bu betda muhim voqea yuz beradi.');

        $this->get(route('books.show', $book->slug))
            ->assertOk()
            ->assertSee('PDF bet belgilari')
            ->assertSee('Bu betda muhim voqea yuz beradi.');
    }

    public function test_pdf_page_annotations_are_private_and_only_owner_can_delete_them(): void
    {
        [$owner, $book] = $this->makePdfBook();
        $visitor = User::create([
            'name' => 'PDF Visitor',
            'username' => 'pdf_visitor_' . uniqid(),
            'email' => uniqid() . '@example.test',
            'password' => bcrypt('password'),
        ]);
        $annotation = PdfAnnotation::create([
            'user_id' => $owner->id,
            'book_id' => $book->id,
            'page_number' => 4,
            'kind' => 'note',
            'note_text' => 'Faqat egasiga ko‘rinadigan izoh.',
        ]);

        $this->actingAs($visitor)
            ->get(route('books.flipbook', $book))
            ->assertOk()
            ->assertDontSee('Faqat egasiga ko‘rinadigan izoh.');

        $this->deleteJson(route('books.pdf.annotations.destroy', $annotation))
            ->assertNotFound();
        $this->assertDatabaseHas('pdf_annotations', ['id' => $annotation->id]);

        $this->actingAs($owner)
            ->deleteJson(route('books.pdf.annotations.destroy', $annotation))
            ->assertOk();
        $this->assertDatabaseMissing('pdf_annotations', ['id' => $annotation->id]);
    }
}
