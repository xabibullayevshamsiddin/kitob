<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Redirector;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Livewire\LivewireComponentsFinder;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use Symfony\Component\Finder\Finder;
use Throwable;

class AuditSystemCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'audit:system
                            {--routes : Only run the HTTP route 500 audit}
                            {--livewire : Only run the Livewire components audit}
                            {--schema : Only run the database schema and query audit}
                            {--all : Run all audits (default)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Comprehensive 500 error detection and health audit for routes, database schemas, and Livewire components.';

    /**
     * Audit statistics.
     */
    protected int $checksCount = 0;
    protected int $passCount = 0;
    protected int $warnCount = 0;
    protected int $failCount = 0;
    protected array $failures = [];
    protected array $warnings = [];

    /**
     * Cached DB schema info.
     */
    protected array $tableColumns = [];
    protected array $allKnownColumns = [];
    protected array $modelTableMap = [];

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('');
        $this->line('========================================================================');
        $this->info('              KITOB PLATFORM - SYSTEM HEALTH & 500 AUDIT               ');
        $this->line('========================================================================');
        $this->info('Environment: ' . config('app.env') . ' | PHP ' . PHP_VERSION . ' | Laravel ' . app()->version());
        $this->info('Database:    ' . config('database.default') . ' (' . config('database.connections.' . config('database.default') . '.database') . ')');
        $this->line('------------------------------------------------------------------------');

        $runRoutes = $this->option('routes');
        $runLivewire = $this->option('livewire');
        $runSchema = $this->option('schema');

        // If none explicitly requested, or --all is passed, run everything
        if (!$runRoutes && !$runLivewire && !$runSchema) {
            $runRoutes = true;
            $runLivewire = true;
            $runSchema = true;
        }

        $startTime = microtime(true);

        if ($runSchema) {
            $this->auditSchemaAndModels();
            $this->auditCodebaseQueries();
        }

        if ($runLivewire) {
            $this->auditLivewireComponents();
        }

        if ($runRoutes) {
            $this->auditHttpRoutes();
        }

        $duration = round(microtime(true) - $startTime, 2);

        $this->printSummary($duration);

        return $this->failCount > 0 ? 1 : 0;
    }

    /**
     * 1. Database Schema and Eloquent Model Audit.
     */
    protected function auditSchemaAndModels(): void
    {
        $this->info('');
        $this->comment('▶ [1/4] AUDITING DATABASE SCHEMA & ELOQUENT MODELS');

        // 1A. Load all database tables and columns
        try {
            $rawTables = DB::select('SHOW TABLES');
            foreach ($rawTables as $rawTable) {
                $tableName = current((array) $rawTable);
                $columns = Schema::getColumnListing($tableName);
                $this->tableColumns[$tableName] = $columns;
                foreach ($columns as $c) {
                    $this->allKnownColumns[$c] = true;
                }
            }
            $this->line(sprintf('  ✓ Loaded %d tables and %d unique columns from database.', count($this->tableColumns), count($this->allKnownColumns)));
        } catch (Throwable $e) {
            $this->recordFailure('Database Connection', 'Failed to inspect database tables: ' . $e->getMessage());
            return;
        }

        // 1B. Verify all Eloquent Models in app/Models
        $modelFiles = glob(app_path('Models/*.php'));
        foreach ($modelFiles as $file) {
            $className = 'App\\Models\\' . basename($file, '.php');
            if (!class_exists($className)) {
                continue;
            }

            try {
                $ref = new ReflectionClass($className);
                if ($ref->isAbstract() || !$ref->isSubclassOf(Model::class)) {
                    continue;
                }

                $this->checksCount++;
                /** @var Model $instance */
                $instance = new $className();
                $table = $instance->getTable();
                $this->modelTableMap[$ref->getShortName()] = $table;
                $this->modelTableMap[$className] = $table;

                // Check table exists
                if (!isset($this->tableColumns[$table])) {
                    $this->recordFailure(
                        "Model [{$className}]",
                        "Database table [{$table}] does not exist!"
                    );
                    continue;
                }

                $tableCols = $this->tableColumns[$table];

                // Check fillable columns exist in table
                foreach ($instance->getFillable() as $fillable) {
                    if (!in_array($fillable, $tableCols, true)) {
                        $this->recordFailure(
                            "Model [{$className}]",
                            "Fillable attribute [{$fillable}] does not exist on table [{$table}]."
                        );
                    }
                }

                // Check casted columns exist in table
                foreach (array_keys($instance->getCasts()) as $castCol) {
                    if ($castCol === $instance->getKeyName()) {
                        continue;
                    }
                    if (!in_array($castCol, $tableCols, true)) {
                        $this->recordFailure(
                            "Model [{$className}]",
                            "Casted attribute [{$castCol}] does not exist on table [{$table}]."
                        );
                    }
                }

                // Test smoke query (catches broken scopes, invalid default orders, etc.)
                try {
                    $className::query()->take(1)->get();
                    $this->passCount++;
                } catch (Throwable $queryException) {
                    $this->recordFailure(
                        "Model Query [{$className}::query()->take(1)]",
                        $queryException->getMessage()
                    );
                }
            } catch (Throwable $e) {
                $this->recordFailure("Model Reflection [{$className}]", $e->getMessage());
            }
        }

        $this->line(sprintf('  ✓ Verified %d Eloquent models against database tables and column definitions.', count($modelFiles)));
    }

    /**
     * 2. Scan PHP code for column typos in queries (e.g. select(['col1', 'bad_col'])).
     */
    protected function auditCodebaseQueries(): void
    {
        $this->info('');
        $this->comment('▶ [2/4] AUDITING CODEBASE ELOQUENT & QUERY COLUMN REFERENCES');

        $finder = (new Finder())
            ->in([app_path('Services'), app_path('Http/Controllers'), app_path('Http/Livewire'), app_path('Console')])
            ->name('*.php')
            ->files();

        $scannedFiles = 0;
        $foundSuspects = 0;

        foreach ($finder as $file) {
            $scannedFiles++;
            $content = $file->getContents();
            $relativePath = $file->getRelativePathname();

            // Match ->select(['...']) or ->get(['...']) or ->pluck('...')
            if (preg_match_all('/->(?:select|get|pluck)\(\s*\[([^\]]+)\]/s', $content, $matches, PREG_OFFSET_CAPTURE)) {
                foreach ($matches[1] as $match) {
                    $rawArray = $match[0];
                    $offset = $match[1];
                    $lineNumber = substr_count(substr($content, 0, $offset), "\n") + 1;

                    // Extract all string literals inside the array
                    if (preg_match_all('/[\'"]([a-zA-Z0-9_]+)[\'"]/', $rawArray, $colMatches)) {
                        foreach ($colMatches[1] as $col) {
                            $this->checksCount++;
                            // Check if column exists anywhere in the database
                            if (!isset($this->allKnownColumns[$col])) {
                                $this->recordFailure(
                                    "Column Reference [{$relativePath}:{$lineNumber}]",
                                    "Unknown column '{$col}' in query selection. This column does not exist anywhere in the database!"
                                );
                                $foundSuspects++;
                            } else {
                                $this->passCount++;
                            }
                        }
                    }
                }
            }
        }

        if ($foundSuspects === 0) {
            $this->line(sprintf('  ✓ Scanned %d application PHP files. Zero missing or misspelled query columns detected.', $scannedFiles));
        }
    }

    /**
     * 3. Livewire Component Lifecycle & Mounting Audit.
     */
    protected function auditLivewireComponents(): void
    {
        $this->info('');
        $this->comment('▶ [3/4] AUDITING LIVEWIRE COMPONENTS MOUNT & BLADE RENDERING');

        /** @var LivewireComponentsFinder $finder */
        $manifest = app(LivewireComponentsFinder::class)->getManifest();
        $livewire = app('livewire');

        // Resolve test entities for dependency injection
        $book = \App\Models\Book::first();
        if (!$book) {
            $book = new \App\Models\Book();
            $book->id = 1;
            $book->title = 'Test Book';
            $book->slug = 'test-book';
        }

        $group = \App\Models\Group::first();
        if (!$group) {
            $group = new \App\Models\Group();
            $group->id = 1;
            $group->name = 'Test Group';
        }

        $quiz = \App\Models\Quiz::first();
        if (!$quiz) {
            $quiz = new \App\Models\Quiz();
            $quiz->id = 1;
            $quiz->title = 'Test Quiz';
            $quiz->book_id = $book->id;
        }

        $liveEvent = \App\Models\LiveEvent::first();
        if (!$liveEvent) {
            $liveEvent = new \App\Models\LiveEvent();
            $liveEvent->id = 1;
            $liveEvent->title = 'Test Stream';
            $liveEvent->status = 'scheduled';
        }

        $admin = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first();

        if ($admin) {
            auth()->guard('web')->login($admin);
            // Ensure admin belongs to test group so group-detail renders
            if ($group->id) {
                try {
                    \App\Models\GroupMember::firstOrCreate(
                        ['group_id' => $group->id, 'user_id' => $admin->id],
                        ['role' => 'admin']
                    );
                } catch (Throwable) {
                    // Ignore if DB locked or in-memory
                }
            }
        }

        $defaultRedirector = new Redirector(app('url'));
        if (app()->has('session.store')) {
            $defaultRedirector->setSession(app('session.store'));
        }

        foreach ($manifest as $alias => $componentClass) {
            $this->checksCount++;

            if (!class_exists($componentClass)) {
                $this->recordFailure("Livewire [{$alias}]", "Class [{$componentClass}] does not exist!");
                continue;
            }

            try {
                // Dynamically resolve mount parameters
                $mountParams = $this->resolveLivewireMountParams($componentClass, [
                    'book'     => $book,
                    'bookId'   => $book->id,
                    'group'    => $group,
                    'groupId'  => $group->id,
                    'quiz'     => $quiz,
                    'event'    => $liveEvent,
                    'eventId'  => $liveEvent->id,
                    'user'     => $admin,
                    'username' => $admin?->username ?? 'admin',
                ]);

                // Mount component
                $response = $livewire->mount($alias, $mountParams);
                $html = $response->html();

                // Check for strict type pitfalls in public properties
                $this->checkLivewirePropertyTypes($componentClass, $alias);

                $this->passCount++;
                $this->line(sprintf('  [PASS] Livewire: %-32s (%d bytes rendered)', $alias, strlen($html)));
            } catch (Throwable $e) {
                $this->recordFailure(
                    "Livewire [{$alias}] ({$componentClass})",
                    $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine()
                );
            } finally {
                // Restore container state after Livewire execution
                $restoredRedirector = clone $defaultRedirector;
                if (app()->has('session.store')) {
                    $restoredRedirector->setSession(app('session.store'));
                }
                app()->instance('redirect', $restoredRedirector);
                Paginator::defaultView('vendor.pagination.tailwind');
            }
        }
    }

    /**
     * 4. Comprehensive HTTP GET Route 500 Audit.
     */
    protected function auditHttpRoutes(): void
    {
        $this->info('');
        $this->comment('▶ [4/4] AUDITING HTTP ROUTES (500 ERROR & EXCEPTION CHECK)');

        $routes = Route::getRoutes()->getRoutesByMethod()['GET'] ?? [];

        $book = \App\Models\Book::first();
        if (!$book) {
            $book = new \App\Models\Book();
            $book->id = 1;
            $book->slug = 'test-slug';
        }

        $group = \App\Models\Group::first();
        if (!$group) {
            $group = new \App\Models\Group();
            $group->id = 1;
        }

        $quiz = \App\Models\Quiz::first();
        if (!$quiz) {
            $quiz = new \App\Models\Quiz();
            $quiz->id = 1;
        }

        $liveEvent = \App\Models\LiveEvent::first();
        if (!$liveEvent) {
            $liveEvent = new \App\Models\LiveEvent();
            $liveEvent->id = 1;
        }

        $admin = \App\Models\User::where('role', 'admin')->first() ?? \App\Models\User::first();
        $reader = \App\Models\User::where('role', 'reader')->first() ?? $admin;

        $defaultRedirector = new Redirector(app('url'));
        if (app()->has('session.store')) {
            $defaultRedirector->setSession(app('session.store'));
        }

        $testedRoutes = 0;

        foreach ($routes as $route) {
            $uri = $route->uri();

            // Skip internal / debug / file preview routes
            if (
                str_starts_with($uri, '_') ||
                str_starts_with($uri, 'livewire') ||
                str_starts_with($uri, 'telescope') ||
                str_starts_with($uri, 'sanctum') ||
                str_starts_with($uri, 'user/confirm-password') ||
                str_starts_with($uri, 'user/confirmed-password-status')
            ) {
                continue;
            }

            // Preview error routes are intentionally returning error codes
            $isErrorPreview = str_starts_with($uri, 'errors/');

            // Parameter substitution
            $targetUri = $uri;
            $targetUri = str_replace(['{book}', '{bookId}', '{bookId?}'], (string) ($book->id ?? 1), $targetUri);
            $targetUri = str_replace('{slug}', (string) ($book->slug ?? 'test-book'), $targetUri);
            $targetUri = str_replace('{group}', (string) ($group->id ?? 1), $targetUri);
            $targetUri = str_replace('{quiz}', (string) ($quiz->id ?? 1), $targetUri);
            $targetUri = str_replace('{event}', (string) ($liveEvent->id ?? 1), $targetUri);
            $targetUri = str_replace('{username}', (string) ($admin?->username ?? 'admin'), $targetUri);
            $targetUri = str_replace(['{tab?}', '{tab}'], 'overview', $targetUri);
            $targetUri = str_replace('{metric}', 'overview', $targetUri);
            $targetUri = str_replace(['{user}', '{id}'], '1', $targetUri);
            $targetUri = str_replace(['{token}', '{filename}', '{path}', '{chapter}', '{video}', '{faq}', '{item}'], '1', $targetUri);

            // Skip routes where dynamic parameters could not be filled
            if (str_contains($targetUri, '{')) {
                continue;
            }

            $this->checksCount++;
            $testedRoutes++;

            // Set up request and session
            $request = Request::create('/' . ltrim($targetUri, '/'), 'GET');
            if (app()->has('session.store')) {
                $session = app('session.store');
                $request->setLaravelSession($session);
                app('redirect')->setSession($session);
            }

            // Determine appropriate auth guard
            auth()->shouldUse('web');
            if (str_starts_with($uri, 'admin')) {
                if ($admin) {
                    auth()->guard('web')->login($admin);
                }
            } elseif (
                $uri === '/' ||
                $uri === 'login' ||
                $uri === 'register' ||
                $uri === 'about' ||
                $uri === 'faq' ||
                $uri === 'terms' ||
                $uri === 'privacy' ||
                $uri === 'rules' ||
                $uri === 'rules-and-penalties' ||
                $uri === 'catalog' ||
                $uri === 'contact'
            ) {
                auth()->guard('web')->logout();
            } else {
                if ($reader) {
                    auth()->guard('web')->login($reader);
                }
            }

            try {
                $response = app()->handle($request);
                $code = $response->getStatusCode();

                if ($isErrorPreview) {
                    $this->passCount++;
                    $this->line(sprintf('  [PASS] Route (Preview): %-40s => HTTP %d', $uri, $code));
                } elseif ($code >= 500) {
                    $excMsg = isset($response->exception)
                        ? $response->exception->getMessage() . ' in ' . $response->exception->getFile() . ':' . $response->exception->getLine()
                        : 'HTTP ' . $code . ' returned without exception payload';

                    $this->recordFailure("Route GET [/{$uri}]", "Server returned HTTP {$code}: {$excMsg}");
                } else {
                    $this->passCount++;
                    // Print clean status line for passed routes
                    $this->line(sprintf('  [PASS] Route: %-42s => HTTP %d', '/' . $uri, $code));
                }
            } catch (Throwable $e) {
                $this->recordFailure("Route GET [/{$uri}]", "Uncaught Exception: " . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
            } finally {
                // Ensure container isolation between route evaluations
                $restoredRedirector = clone $defaultRedirector;
                if (app()->has('session.store')) {
                    $restoredRedirector->setSession(app('session.store'));
                }
                app()->instance('redirect', $restoredRedirector);
                Paginator::defaultView('vendor.pagination.tailwind');
            }
        }

        $this->line(sprintf('  ✓ Completed HTTP verification for %d GET routes.', $testedRoutes));
    }

    /**
     * Resolve mount arguments for Livewire component via Reflection.
     */
    protected function resolveLivewireMountParams(string $componentClass, array $available): array
    {
        if (!method_exists($componentClass, 'mount')) {
            return [];
        }

        $refMethod = new ReflectionMethod($componentClass, 'mount');
        $params = [];

        foreach ($refMethod->getParameters() as $param) {
            $name = $param->getName();
            $type = $param->getType();

            if ($type instanceof ReflectionNamedType) {
                $typeName = $type->getName();

                if ($typeName === \App\Models\Book::class && isset($available['book'])) {
                    $params[$name] = $available['book'];
                } elseif ($typeName === \App\Models\Group::class && isset($available['group'])) {
                    $params[$name] = $available['group'];
                } elseif ($typeName === \App\Models\Quiz::class && isset($available['quiz'])) {
                    $params[$name] = $available['quiz'];
                } elseif ($typeName === \App\Models\LiveEvent::class && isset($available['event'])) {
                    $params[$name] = $available['event'];
                } elseif ($typeName === \App\Models\User::class && isset($available['user'])) {
                    $params[$name] = $available['user'];
                } elseif ($typeName === 'string' && $name === 'username' && isset($available['username'])) {
                    $params[$name] = $available['username'];
                } elseif ($typeName === 'int' && str_contains(strtolower($name), 'id')) {
                    $params[$name] = 1;
                }
            } elseif (isset($available[$name])) {
                $params[$name] = $available[$name];
            }
        }

        return $params;
    }

    /**
     * Inspect Livewire component properties for potential type crash pitfalls.
     */
    protected function checkLivewirePropertyTypes(string $componentClass, string $alias): void
    {
        $ref = new ReflectionClass($componentClass);
        foreach ($ref->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            if ($prop->isStatic()) {
                continue;
            }
            $type = $prop->getType();
            if ($type instanceof ReflectionNamedType) {
                $tName = $type->getName();
                // Flag int or float properties that don't allow null or string
                // In Livewire, <select> or <input> default empty string throws fatal TypeError on sync
                if (($tName === 'int' || $tName === 'float') && !$type->allowsNull()) {
                    $this->warnCount++;
                    $this->warnings[] = [
                        'source'  => "Livewire [{$alias}]",
                        'message' => "Public property \${$prop->getName()} is strictly typed as [{$tName}] without nullable (?{$tName}). If bound to wire:model on a form input or select sending empty string, it will throw a fatal 500 TypeError.",
                    ];
                }
            }
        }
    }

    /**
     * Record a failure.
     */
    protected function recordFailure(string $source, string $message): void
    {
        $this->failCount++;
        $this->failures[] = [
            'source'  => $source,
            'message' => $message,
        ];
        $this->error("  [FAIL] {$source}: {$message}");
    }

    /**
     * Print formatted summary table.
     */
    protected function printSummary(float $duration): void
    {
        $this->info('');
        $this->line('========================================================================');
        $this->info('                           AUDIT SUMMARY                                ');
        $this->line('========================================================================');
        $this->line(sprintf('  Total Checks Performed:  <info>%d</info>', $this->checksCount));
        $this->line(sprintf('  Passed:                  <info>%d</info>', $this->passCount));
        $this->line(sprintf('  Warnings:                <comment>%d</comment>', $this->warnCount));
        $this->line(sprintf('  Errors / 500s Detected:  <%s>%d</%s>', $this->failCount > 0 ? 'error' : 'info', $this->failCount, $this->failCount > 0 ? 'error' : 'info'));
        $this->line(sprintf('  Execution Time:          %s seconds', $duration));
        $this->line('------------------------------------------------------------------------');

        if (!empty($this->warnings)) {
            $this->comment('WARNINGS (Non-breaking potential issues):');
            foreach ($this->warnings as $warn) {
                $this->line("  ⚠ {$warn['source']}: {$warn['message']}");
            }
            $this->line('------------------------------------------------------------------------');
        }

        if (!empty($this->failures)) {
            $this->error('FAILURES (Critical 500 errors or schema violations):');
            foreach ($this->failures as $i => $fail) {
                $this->line(sprintf('  %d) <error>%s</error>', $i + 1, $fail['source']));
                $this->line("     {$fail['message']}");
            }
            $this->line('========================================================================');
            $this->error('❌ AUDIT FAILED! Address the errors above to prevent production 500 errors.');
        } else {
            $this->line('========================================================================');
            $this->info('✅ ALL AUDIT CHECKS PASSED! Zero 500 errors or broken schemas found.');
        }
        $this->info('');
    }
}
