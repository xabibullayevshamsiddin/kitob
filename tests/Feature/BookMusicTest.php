<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\BookChapter;
use App\Models\BookMusic;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class BookMusicTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $student;
    protected Book $book;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);

        $this->admin = User::create([
            'name'              => 'Admin Test',
            'username'          => 'admintest',
            'email'             => 'admintest@example.com',
            'password'          => Hash::make('secret123'),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);
        $this->admin->syncRoles(['admin']);

        $this->student = User::create([
            'name'              => 'Student Test',
            'username'          => 'studenttest',
            'email'             => 'studenttest@example.com',
            'password'          => Hash::make('secret123'),
            'role'              => 'student',
            'email_verified_at' => now(),
        ]);
        $this->student->syncRoles(['student']);

        $this->book = Book::create([
            'title'        => 'Ufq Romani',
            'slug'         => 'ufq-romani',
            'author'       => 'Said Ahmad',
            'description'  => 'Tarixiy asar',
            'genre'        => 'Roman',
            'week_number'  => 1,
            'published_at' => now(),
            'is_active'    => true,
        ]);
    }

    public function test_admin_can_view_book_music_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.books.music.index', $this->book->id));

        $response->assertOk();
        $response->assertSee('Kitob fon musiqalari: «Ufq Romani»');
    }

    public function test_non_admin_cannot_view_or_manage_book_music(): void
    {
        $response = $this->actingAs($this->student)->get(route('admin.books.music.index', $this->book->id));

        $response->assertForbidden();
    }

    public function test_admin_can_add_custom_uploaded_music_to_book(): void
    {
        Storage::fake('public');

        $fakeAudio = UploadedFile::fake()->create('relaxing_piano.mp3', 1024, 'audio/mpeg');

        $response = $this->actingAs($this->admin)->post(route('admin.books.music.store', $this->book->id), [
            'title'      => 'Sokin Pianino Ohangi',
            'music_file' => $fakeAudio,
            'order'      => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('book_musics', [
            'book_id' => $this->book->id,
            'title'   => 'Sokin Pianino Ohangi',
            'order'   => 1,
            'is_active' => true,
        ]);

        $music = BookMusic::where('book_id', $this->book->id)->first();
        $this->assertNotNull($music);
        Storage::disk('public')->assertExists($music->file_path);
    }

    public function test_admin_can_add_multiple_music_tracks_to_single_book(): void
    {
        Storage::fake('public');

        // 1-musiqa
        $this->actingAs($this->admin)->post(route('admin.books.music.store', $this->book->id), [
            'title'      => '1-Trek: Yomg\'ir',
            'music_file' => UploadedFile::fake()->create('rain.mp3', 512, 'audio/mpeg'),
            'order'      => 1,
        ]);

        // 2-musiqa
        $this->actingAs($this->admin)->post(route('admin.books.music.store', $this->book->id), [
            'title'      => '2-Trek: Klassik Pianino',
            'music_file' => UploadedFile::fake()->create('piano.mp3', 512, 'audio/mpeg'),
            'order'      => 2,
        ]);

        // 3-musiqa
        $this->actingAs($this->admin)->post(route('admin.books.music.store', $this->book->id), [
            'title'      => '3-Trek: Tabiat Sadosi',
            'music_file' => UploadedFile::fake()->create('nature.mp3', 512, 'audio/mpeg'),
            'order'      => 3,
        ]);

        $this->assertSame(3, $this->book->musics()->count());
        $this->assertSame(3, $this->book->activeMusics()->count());
    }

    public function test_admin_can_toggle_music_active_status(): void
    {
        $music = BookMusic::create([
            'book_id'   => $this->book->id,
            'title'     => 'Kamin Olovi',
            'file_path' => 'sounds/ambient/rain.wav',
            'order'     => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->patch(
            route('admin.books.music.toggle', ['book' => $this->book->id, 'music' => $music->id])
        );

        $response->assertRedirect();
        $this->assertFalse($music->fresh()->is_active);
    }

    public function test_admin_can_delete_music(): void
    {
        Storage::fake('public');

        $music = BookMusic::create([
            'book_id'   => $this->book->id,
            'title'     => 'Eski trek',
            'file_path' => 'books/music/test.mp3',
            'order'     => 1,
            'is_active' => true,
        ]);

        Storage::disk('public')->put('books/music/test.mp3', 'dummy audio');

        $response = $this->actingAs($this->admin)->delete(
            route('admin.books.music.destroy', ['book' => $this->book->id, 'music' => $music->id])
        );

        $response->assertRedirect();
        $this->assertDatabaseMissing('book_musics', ['id' => $music->id]);
        Storage::disk('public')->assertMissing('books/music/test.mp3');
    }

    public function test_reader_contains_ambient_music_player(): void
    {
        $chapter = BookChapter::create([
            'book_id'        => $this->book->id,
            'chapter_number' => 1,
            'title'          => '1-Bob Kirish',
            'content'        => 'Bobning sinov matni...',
            'is_published'   => true,
        ]);

        BookMusic::create([
            'book_id'   => $this->book->id,
            'title'     => 'Orombaxsh Oqshom',
            'file_path' => 'sounds/ambient/piano.wav',
            'order'     => 1,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->student)->get(
            route('reader.show', ['book' => $this->book->id, 'chapter' => $chapter->id])
        );

        $response->assertOk();
        $response->assertSee('ambient-music-scope', false);
        $response->assertSee('Orombaxsh Oqshom');
        $response->assertSee('Mutolaa Fon Musiqasi');
    }
}
