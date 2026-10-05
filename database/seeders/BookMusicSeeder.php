<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookMusic;
use Illuminate\Database\Seeder;

class BookMusicSeeder extends Seeder
{
    public function run(): void
    {
        $books = Book::all();

        foreach ($books as $book) {
            if ($book->musics()->count() === 0) {
                BookMusic::create([
                    'book_id'   => $book->id,
                    'title'     => 'Sokin Yomg\'ir & Tabiat',
                    'file_path' => 'sounds/ambient/rain.wav',
                    'order'     => 1,
                    'is_active' => true,
                ]);

                BookMusic::create([
                    'book_id'   => $book->id,
                    'title'     => 'Klassik Pianino Oromi',
                    'file_path' => 'sounds/ambient/piano.wav',
                    'order'     => 2,
                    'is_active' => true,
                ]);

                BookMusic::create([
                    'book_id'   => $book->id,
                    'title'     => 'O\'rmon Shabodasi & Qushlar',
                    'file_path' => 'sounds/ambient/forest.wav',
                    'order'     => 3,
                    'is_active' => true,
                ]);
            }
        }
    }
}
