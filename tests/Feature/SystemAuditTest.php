<?php

namespace Tests\Feature;

use App\Models\Book;
use App\Models\Group;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireComponentsFinder;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Tests\TestCase;
use Throwable;

class SystemAuditTest extends TestCase
{
    /**
     * Test that the audit:system artisan command executes cleanly with exit code 0.
     */
    public function test_audit_command_runs_successfully(): void
    {
        $this->artisan('audit:system')
            ->assertExitCode(0);
    }

    /**
     * Test that all Eloquent models match real database tables, fillables, and casts.
     */
    public function test_all_eloquent_models_match_database_tables_and_columns(): void
    {
        $tables = array_map(fn ($t) => current((array) $t), DB::select('SHOW TABLES'));
        $tableColumns = [];
        foreach ($tables as $t) {
            $tableColumns[$t] = Schema::getColumnListing($t);
        }

        $modelFiles = glob(app_path('Models/*.php'));
        $this->assertNotEmpty($modelFiles, 'No model files found in app/Models.');

        foreach ($modelFiles as $file) {
            $className = 'App\\Models\\' . basename($file, '.php');
            if (!class_exists($className)) {
                continue;
            }

            $ref = new ReflectionClass($className);
            if ($ref->isAbstract() || !$ref->isSubclassOf(Model::class)) {
                continue;
            }

            /** @var Model $instance */
            $instance = new $className();
            $table = $instance->getTable();

            $this->assertArrayHasKey(
                $table,
                $tableColumns,
                "Model [{$className}] specifies table [{$table}] which does not exist in the database!"
            );

            $cols = $tableColumns[$table];

            foreach ($instance->getFillable() as $fillable) {
                $this->assertContains(
                    $fillable,
                    $cols,
                    "Model [{$className}] has fillable attribute [{$fillable}] missing from table [{$table}]."
                );
            }

            foreach (array_keys($instance->getCasts()) as $castCol) {
                if ($castCol === $instance->getKeyName()) {
                    continue;
                }
                $this->assertContains(
                    $castCol,
                    $cols,
                    "Model [{$className}] has casted attribute [{$castCol}] missing from table [{$table}]."
                );
            }
        }
    }

    /**
     * Test that no codebase query references nonexistent columns.
     */
    public function test_no_unknown_columns_referenced_in_codebase_queries(): void
    {
        $tables = array_map(fn ($t) => current((array) $t), DB::select('SHOW TABLES'));
        $allKnownCols = [];
        foreach ($tables as $t) {
            foreach (Schema::getColumnListing($t) as $col) {
                $allKnownCols[$col] = true;
            }
        }

        $finder = (new Finder())
            ->in([app_path('Services'), app_path('Http/Controllers'), app_path('Http/Livewire')])
            ->name('*.php')
            ->files();

        $unknown = [];

        foreach ($finder as $file) {
            $content = $file->getContents();
            if (preg_match_all('/->(?:select|get|pluck)\(\s*\[([^\]]+)\]/s', $content, $matches)) {
                foreach ($matches[1] as $raw) {
                    if (preg_match_all('/[\'"]([a-zA-Z0-9_]+)[\'"]/', $raw, $cols)) {
                        foreach ($cols[1] as $c) {
                            if (!isset($allKnownCols[$c])) {
                                $unknown[] = $file->getRelativePathname() . " -> [{$c}]";
                            }
                        }
                    }
                }
            }
        }

        $this->assertEmpty($unknown, "Found queries referencing unknown columns:\n" . implode("\n", $unknown));
    }

    /**
     * Test that core public GET routes return non-500 HTTP statuses.
     */
    public function test_public_routes_do_not_return_500(): void
    {
        $publicRoutes = [
            '/',
            '/about',
            '/contact',
            '/faq',
            '/terms',
            '/privacy',
            '/catalog',
            '/login',
            '/register',
            '/leaderboard',
        ];

        foreach ($publicRoutes as $uri) {
            $response = $this->get($uri);
            $this->assertLessThan(
                500,
                $response->getStatusCode(),
                "Route [{$uri}] returned 500 error: " . ($response->exception ? $response->exception->getMessage() : '')
            );
        }
    }

    /**
     * Test that Livewire components can mount without throwing 500 exceptions.
     */
    public function test_livewire_components_can_mount_and_render(): void
    {
        $manifest = app(LivewireComponentsFinder::class)->getManifest();
        $livewire = app('livewire');

        $book = Book::first() ?? new Book(['id' => 1, 'slug' => 'test-book']);
        $group = Group::first() ?? new Group(['id' => 1]);
        $quiz = Quiz::first() ?? new Quiz(['id' => 1, 'book_id' => $book->id]);
        $user = User::first() ?? new User(['id' => 1, 'username' => 'testuser']);

        $paramsMap = [
            'book.book-assistant'       => ['bookId' => $book->id],
            'book.book-discussion-chat' => ['book' => $book],
            'book.buddy-read-modal'     => ['book' => $book],
            'groups.group-detail'       => ['group' => $group],
            'quiz.take-quiz'            => ['book' => $book],
            'profile.profile-page'      => ['username' => $user->username],
        ];

        foreach ($manifest as $alias => $class) {
            $params = $paramsMap[$alias] ?? [];
            try {
                $response = $livewire->mount($alias, $params);
                $this->assertNotNull($response, "Component [{$alias}] failed to mount.");
            } catch (Throwable $e) {
                // If it is an expected auth redirect, allow it
                if (str_contains($e->getMessage(), 'login') || str_contains($e->getMessage(), 'Route')) {
                    continue;
                }
                $this->fail("Livewire component [{$alias}] threw fatal exception: " . $e->getMessage());
            }
        }
    }
}
