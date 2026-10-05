<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class SettingsController extends Controller
{
    /**
     * Display system settings and diagnostics dashboard.
     */
    public function index(): View
    {
        // 1. Hisob-kitoblar va Diagnostika
        $isMaintenance = app()->isDownForMaintenance();
        $isSymlinkValid = is_link(public_path('storage')) || file_exists(public_path('storage'));

        // Ma'lumotlar bazasi hajmi va jadvallar soni
        $dbSizeMb = 0;
        $dbTablesCount = 0;
        try {
            $dbName = DB::connection()->getDatabaseName();
            $tablesData = DB::select("
                SELECT table_name, round(((data_length + index_length) / 1024 / 1024), 2) as size_mb
                FROM information_schema.TABLES
                WHERE table_schema = ?
            ", [$dbName]);

            $dbTablesCount = count($tablesData);
            $dbSizeMb = round(array_sum(array_column($tablesData, 'size_mb')), 2);
        } catch (\Throwable $e) {
            $dbSizeMb = 'N/A';
            $dbTablesCount = 'N/A';
        }

        // Storage / Fayllar hajmi
        $storagePath = storage_path('app/public');
        $storageSizeMb = 0;
        if (File::exists($storagePath)) {
            $storageBytes = 0;
            foreach (File::allFiles($storagePath) as $file) {
                $storageBytes += $file->getSize();
            }
            $storageSizeMb = round($storageBytes / 1024 / 1024, 2);
        }

        $systemStats = [
            'php_version'        => PHP_VERSION,
            'laravel_version'    => app()->version(),
            'os'                 => PHP_OS_FAMILY,
            'environment'        => config('app.env', 'production'),
            'debug_mode'         => config('app.debug', false),
            'cache_driver'       => config('cache.default', 'file'),
            'session_driver'     => config('session.driver', 'file'),
            'database_driver'    => config('database.default', 'mysql'),
            'database_name'      => config('database.connections.mysql.database', 'kitob'),
            'database_size_mb'   => $dbSizeMb,
            'database_tables'    => $dbTablesCount,
            'storage_size_mb'    => $storageSizeMb,
            'storage_symlink'    => $isSymlinkValid,
            'is_maintenance'     => $isMaintenance,
            'upload_max_filesize'=> ini_get('upload_max_filesize'),
            'post_max_size'      => ini_get('post_max_size'),
            'memory_limit'       => ini_get('memory_limit'),
        ];

        // 2. Mavjud sozlamalarni yuklash
        $settings = Setting::getAllGrouped();

        return view('admin.settings', [
            'systemStats'   => $systemStats,
            'settings'      => $settings,
            'isMaintenance' => $isMaintenance,
        ]);
    }

    /**
     * Update platform settings and synchronize with .env if needed.
     */
    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            // General
            'app_name'                     => 'required|string|min:2|max:100',
            'contact_email'                => 'required|email|max:100',
            'app_url'                      => 'required|url|max:255',
            'timezone'                     => 'required|string|max:50',
            'per_page'                     => 'required|integer|min:5|max:100',
            'welcome_message'              => 'nullable|string|max:255',
            'telegram_channel'             => 'nullable|string|max:100',

            // Gamification
            'reading_points_per_minute'    => 'required|integer|min:1|max:1000',
            'reading_coins_per_minute'     => 'required|integer|min:0|max:100',
            'streak_minimum_minutes'       => 'required|integer|min:1|max:120',
            'quiz_passing_percent'         => 'required|integer|min:10|max:100',
            'welcome_bonus_coins'          => 'required|integer|min:0|max:1000',

            // Community
            'user_group_membership_limit'   => 'required|integer|min:1|max:50',
            'teacher_group_membership_limit'=> 'required|integer|min:1|max:100',
        ], [
            'app_name.required'                  => 'Platforma nomini kiriting.',
            'contact_email.required'             => 'Aloqa email manzilini kiriting.',
            'app_url.required'                   => 'Sayt URL manzilini to\'g\'ri kiriting.',
            'reading_points_per_minute.required' => 'Daqiqa uchun ball miqdorini kiriting.',
        ]);

        // General guruh
        Setting::set('app_name', trim($validated['app_name']), 'general', 'string');
        Setting::set('contact_email', trim($validated['contact_email']), 'general', 'string');
        Setting::set('app_url', rtrim(trim($validated['app_url']), '/'), 'general', 'string');
        Setting::set('timezone', trim($validated['timezone']), 'general', 'string');
        Setting::set('per_page', (int) $validated['per_page'], 'general', 'integer');
        Setting::set('welcome_message', trim($validated['welcome_message'] ?? ''), 'general', 'string');
        Setting::set('telegram_channel', trim($validated['telegram_channel'] ?? ''), 'general', 'string');

        // Gamification guruh
        Setting::set('reading_points_per_minute', (int) $validated['reading_points_per_minute'], 'gamification', 'integer');
        Setting::set('reading_coins_per_minute', (int) $validated['reading_coins_per_minute'], 'gamification', 'integer');
        Setting::set('streak_minimum_minutes', (int) $validated['streak_minimum_minutes'], 'gamification', 'integer');
        Setting::set('quiz_passing_percent', (int) $validated['quiz_passing_percent'], 'gamification', 'integer');
        Setting::set('welcome_bonus_coins', (int) $validated['welcome_bonus_coins'], 'gamification', 'integer');

        // Community guruh
        Setting::set('global_chat_enabled', $request->has('global_chat_enabled'), 'community', 'boolean');
        Setting::set('registration_open', $request->has('registration_open'), 'community', 'boolean');
        Setting::set('user_group_membership_limit', (int) $validated['user_group_membership_limit'], 'community', 'integer');
        Setting::set('teacher_group_membership_limit', (int) $validated['teacher_group_membership_limit'], 'community', 'integer');

        // .env fayliga asosiy o'zgaruvchilarni sinxron yozish
        $this->updateEnvFile([
            'APP_NAME'          => '"' . addslashes(trim($validated['app_name'])) . '"',
            'APP_URL'           => rtrim(trim($validated['app_url']), '/'),
            'MAIL_FROM_ADDRESS' => trim($validated['contact_email']),
            'MAIL_FROM_NAME'    => '"' . addslashes(trim($validated['app_name'])) . '"',
        ]);

        return redirect()->route('admin.settings')->with('success', 'Platforma sozlamalari muvaffaqiyatli saqlandi va yangilandi! ✨');
    }

    /**
     * Cache management handler with all actions.
     */
    public function cache(Request $request): RedirectResponse
    {
        $action = $request->input('action') ?? $request->input('type') ?? 'clear';

        try {
            switch ($action) {
                case 'config':
                    Artisan::call('config:clear');
                    $msg = 'Konfiguratsiya keshi tozalandi va yangilandi! ⚙️';
                    break;

                case 'route':
                    Artisan::call('route:clear');
                    $msg = 'Yo\'nalishlar (route) keshi muvaffaqiyatli tozalandi! 🧭';
                    break;

                case 'view':
                    Artisan::call('view:clear');
                    $msg = 'Blade andozalari (view) keshi tozalandi! 🎨';
                    break;

                case 'clear':
                default:
                    Artisan::call('optimize:clear');
                    Artisan::call('cache:clear');
                    $msg = 'Barcha tizim keshlari (cache, route, view, config) muvaffaqiyatli tozalandi! 🧹';
                    break;
            }

            return back()->with('success', $msg);
        } catch (\Throwable $e) {
            return back()->with('error', 'Keshni yangilashda xatolik yuz berdi: ' . $e->getMessage());
        }
    }

    /**
     * Toggle Maintenance mode on/off.
     */
    public function maintenance(Request $request): RedirectResponse
    {
        $shouldEnable = $request->boolean('maintenance') || $request->input('maintenance') === '1';

        try {
            if ($shouldEnable) {
                Artisan::call('down', [
                    '--secret' => 'admin-bypass',
                ]);
                $msg = "⚠️ Texnik xizmat ko'rsatish rejimi yoqildi! Oddiy foydalanuvchilar uchun sayt vaqtincha yopildi (Adminlar barcha sahifalarga bemalol kira oladi).";
                $cookie = \Illuminate\Foundation\Http\MaintenanceModeBypassCookie::create('admin-bypass');
                return back()->withCookie($cookie)->with('success', $msg);
            } else {
                Artisan::call('up');
                $msg = "✅ Texnik xizmat ko'rsatish rejimi o'chirildi! Sayt barcha foydalanuvchilar uchun ochildi.";
                return back()->withCookie(\Illuminate\Support\Facades\Cookie::forget('laravel_maintenance'))->with('success', $msg);
            }
        } catch (\Throwable $e) {
            return back()->with('error', 'Texnik xizmat rejimini o\'zgartirishda xatolik: ' . $e->getMessage());
        }
    }

    /**
     * Repair or create public storage symlink.
     */
    public function storageLink(): RedirectResponse
    {
        try {
            Artisan::call('storage:link');
            return back()->with('success', 'Fayllar xotirasi (storage:link) muvaffaqiyatli ulandi! 🔗');
        } catch (\Throwable $e) {
            return back()->with('error', 'Storage link yaratishda xatolik: ' . $e->getMessage());
        }
    }

    /**
     * Send test email to check mail server connection.
     */
    public function testEmail(Request $request): RedirectResponse
    {
        $request->validate([
            'test_email' => 'required|email',
        ]);

        $recipient = $request->input('test_email');
        $appName = Setting::get('app_name', config('app.name'));

        try {
            Mail::raw("Salom! Bu {$appName} platformasidan yuborilgan sinov xabari.\n\nEmail tizimi va SMTP server to'g'ri sozlangan va bekamu-ko'st ishlamoqda! ✅", function ($message) use ($recipient, $appName) {
                $message->to($recipient)
                    ->subject("{$appName} — Tizim sinov xabari");
            });

            return back()->with('success', "«{$recipient}» manziliga sinov xabari muvaffaqiyatli yuborildi! ✉️");
        } catch (\Throwable $e) {
            return back()->with('error', "Email yuborishda xatolik: " . $e->getMessage());
        }
    }

    /**
     * Safely update .env variables without breaking the file.
     */
    protected function updateEnvFile(array $values): void
    {
        $envPath = base_path('.env');
        if (!File::exists($envPath) || !is_writable($envPath)) {
            return;
        }

        $envContent = File::get($envPath);

        foreach ($values as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, "{$key}={$value}", $envContent);
            } else {
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);
    }
}
