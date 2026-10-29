<?php

/**
 * EliteHost (Pterodactyl) Management CLI Tool
 *
 * Usage:
 *   php scripts/elitehost.php status
 *   php scripts/elitehost.php restart
 *   php scripts/elitehost.php get <remote_path>
 *   php scripts/elitehost.php put <remote_path> <content_or_file>
 *   php scripts/elitehost.php del <remote_path>
 *   php scripts/elitehost.php list [directory]
 */

class EliteHost
{
    private const BASE_URL = 'https://console.elitehost.uz/api/client';
    private const SERVER_ID= 'b3357df7';

    public static function getApiKey(): string
    {
        if ($envKey = getenv('ELITEHOST_API_KEY')) {
            return trim($envKey);
        }
        if (!empty($_SERVER['ELITEHOST_API_KEY'])) {
            return trim($_SERVER['ELITEHOST_API_KEY']);
        }
        $keyFiles = [
            __DIR__ . '/../.elitehost.key',
            __DIR__ . '/.elitehost.key',
        ];
        foreach ($keyFiles as $file) {
            if (file_exists($file)) {
                $val = trim((string) file_get_contents($file));
                if (!empty($val)) {
                    return $val;
                }
            }
        }
        // Try reading ELITEHOST_API_KEY from root .env
        $envPath = __DIR__ . '/../.env';
        if (file_exists($envPath)) {
            $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), 'ELITEHOST_API_KEY=')) {
                    return trim(explode('=', $line, 2)[1]);
                }
            }
        }
        throw new RuntimeException("EliteHost API kaliti topilmadi. .elitehost.key fayliga yoki ELITEHOST_API_KEY muhit o'zgaruvchisiga kiriting.");
    }

    public static function request(string $method, string $endpoint, $data = null, array $headers = []): array
    {
        $url = self::BASE_URL . $endpoint;
        $ch = curl_init($url);

        $defaultHeaders = [
            'Authorization: Bearer ' . self::getApiKey(),
            'Accept: Application/vnd.pterodactyl.v1+json',
        ];

        if (is_array($data)) {
            $defaultHeaders[] = 'Content-Type: application/json';
            $payload = json_encode($data);
        } elseif (is_string($data)) {
            $defaultHeaders[] = 'Content-Type: text/plain';
            $payload = $data;
        } else {
            $payload = null;
        }

        $allHeaders = array_merge($defaultHeaders, $headers);

        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $allHeaders);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);

        if ($payload !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        }

        $attempts = 0;
        $maxAttempts = 3;
        do {
            $attempts++;
            $response = curl_exec($ch);
            $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $err = curl_error($ch);
            if ($code > 0) {
                break;
            }
            if ($attempts < $maxAttempts) {
                usleep(500000); // 0.5s pause before retry
            }
        } while ($attempts < $maxAttempts);

        curl_close($ch);

        return [
            'code'     => $code,
            'body'     => $response,
            'json'     => json_decode((string) $response, true),
            'error'    => $err,
        ];
    }

    public static function status(): void
    {
        $res = self::request('GET', '/servers/' . self::SERVER_ID . '/resources');
        if ($res['code'] === 200 && isset($res['json']['attributes'])) {
            $attr = $res['json']['attributes'];
            $resrc = $attr['resources'];
            $memMb = round($resrc['memory_bytes'] / 1024 / 1024, 1);
            $diskMb = round($resrc['disk_bytes'] / 1024 / 1024, 1);
            echo "✅ Server Holati: " . strtoupper($attr['current_state']) . "\n";
            echo "   RAM:   {$memMb} MB / 4096 MB\n";
            echo "   CPU:   {$resrc['cpu_absolute']}%\n";
            echo "   Disk:  {$diskMb} MB\n";
            echo "   Uptime: {$resrc['uptime']} soniya\n";
        } else {
            echo "❌ Xatolik: HTTP " . $res['code'] . "\n" . $res['body'] . "\n";
        }
    }

    public static function restart(): void
    {
        $res = self::request('POST', '/servers/' . self::SERVER_ID . '/power', ['signal' => 'restart']);
        echo ($res['code'] === 204)
            ? "✅ Server muvaffaqiyatli qayta ishga tushirildi (Restarting)!\n"
            : "❌ Xatolik: HTTP {$res['code']}\n{$res['body']}\n";
    }

    public static function listFiles(string $dir = '/'): void
    {
        $res = self::request('GET', '/servers/' . self::SERVER_ID . '/files/list?directory=' . urlencode($dir));
        if ($res['code'] === 200 && isset($res['json']['data'])) {
            echo "📂 Jild: {$dir}\n";
            foreach ($res['json']['data'] as $item) {
                $f = $item['attributes'];
                $type = $f['is_file'] ? '📄' : '📁';
                $size = $f['is_file'] ? round($f['size'] / 1024, 1) . ' KB' : '-';
                echo "  {$type} " . str_pad($f['name'], 30) . " ({$size}) [{$f['mode_bits']}]\n";
            }
        } else {
            echo "❌ Xatolik: HTTP {$res['code']}\n{$res['body']}\n";
        }
    }

    public static function getFile(string $remotePath): string
    {
        $res = self::request('GET', '/servers/' . self::SERVER_ID . '/files/contents?file=' . urlencode($remotePath));
        if ($res['code'] === 200) {
            return (string) $res['body'];
        }
        echo "❌ Fayl topilmadi yoki o'qib bo'lmadi: {$remotePath} (HTTP {$res['code']})\n";
        return '';
    }

    public static function putFile(string $remotePath, string $content): bool
    {
        $res = self::request('POST', '/servers/' . self::SERVER_ID . '/files/write?file=' . urlencode($remotePath), $content);
        if ($res['code'] === 204) {
            echo "✅ Fayl muvaffaqiyatli yozildi: {$remotePath}\n";
            return true;
        }
        echo "❌ Fayl yozilmadi: {$remotePath} (HTTP {$res['code']})\n{$res['body']}\n";
        return false;
    }

    public static function syncFile(string $localRelativePath): bool
    {
        $fullPath = __DIR__ . '/../' . ltrim($localRelativePath, '/\\');
        if (!file_exists($fullPath)) {
            echo "❌ Lokal fayl topilmadi: {$fullPath}\n";
            return false;
        }
        $content = file_get_contents($fullPath);
        $remotePath = str_replace('\\', '/', $localRelativePath);
        return self::putFile($remotePath, $content);
    }

    public static function createSchedule(string $name, string $minute, string $hour, array $tasks = []): array
    {
        $payload = [
            'name'             => $name,
            'is_active'        => true,
            'minute'           => $minute,
            'hour'             => $hour,
            'day_of_month'     => '*',
            'month'            => '*',
            'day_of_week'      => '*',
            'only_when_online' => false,
        ];

        $res = self::request('POST', '/servers/' . self::SERVER_ID . '/schedules', $payload);
        if ($res['code'] !== 200 && $res['code'] !== 201) {
            echo "❌ Jadval yaratilmadi: HTTP {$res['code']}\n{$res['body']}\n";
            return $res;
        }

        $scheduleId = $res['json']['attributes']['id'];
        echo "✅ Jadval yaratildi: [{$scheduleId}] {$name} ({$hour}:{$minute})\n";

        foreach ($tasks as $task) {
            $taskPayload = [
                'action'               => $task['action'], // 'power', 'backup', 'command'
                'payload'              => $task['payload'] ?? '',
                'time_offset'          => (int) ($task['time_offset'] ?? 0),
                'continue_on_failure'  => (bool) ($task['continue_on_failure'] ?? false),
            ];
            $tRes = self::request('POST', '/servers/' . self::SERVER_ID . '/schedules/' . $scheduleId . '/tasks', $taskPayload);
            if ($tRes['code'] === 200 || $tRes['code'] === 201) {
                echo "   ➕ Vazifa qo'shildi: {$task['action']} ({$task['payload']})\n";
            } else {
                echo "   ⚠️ Vazifa qo'shilmadi: HTTP {$tRes['code']}\n";
            }
        }

        return $res;
    }

    public static function setupOptionA(): void
    {
        echo "⏰ A-variant bo'yicha jadvallarni o'rnatish:\n\n";

        // 1. Avtomatik Zaxira (Backup) - Har kecha Toshkent vaqti bilan 03:00 (22:00 UTC)
        self::createSchedule('Avtomatik Zaxira (Har kecha 03:00)', '0', '22', [
            [
                'action'              => 'backup',
                'payload'             => '',
                'time_offset'         => 0,
                'continue_on_failure' => true,
            ]
        ]);

        echo "\n";

        // 2. Kechki Qayta Yuklash (Restart) - Har kecha Toshkent vaqti bilan 04:00 (23:00 UTC)
        self::createSchedule('Kechki Qayta Yuklash (Har kecha 04:00)', '0', '23', [
            [
                'action'              => 'power',
                'payload'             => 'restart',
                'time_offset'         => 0,
                'continue_on_failure' => false,
            ]
        ]);

        echo "\n🎉 Jadvallar muvaffaqiyatli o'rnatildi!\n";
    }

    public static function listSchedules(): void
    {
        $res = self::request('GET', '/servers/' . self::SERVER_ID . '/schedules');
        if ($res['code'] === 200 && isset($res['json']['data'])) {
            $list = $res['json']['data'];
            if (empty($list)) {
                echo "ℹ️ Hozircha birorta ham jadval yaratilmagan.\n";
                return;
            }
            echo "📅 Mavjud Jadvallar:\n";
            foreach ($list as $s) {
                $attr = $s['attributes'];
                $status = $attr['is_active'] ? '🟢 Faol' : '⚪️ Noo\'rin';
                echo "  [#{$attr['id']}] {$attr['name']} ({$attr['cron']['hour']}:{$attr['cron']['minute']}) — {$status}\n";
            }
        } else {
            echo "❌ Xatolik: HTTP {$res['code']}\n{$res['body']}\n";
        }
    }

    public static function replaceInFile(string $remotePath, string $search, string $replace): bool
    {
        $content = self::getFile($remotePath);
        if ($content === '') {
            return false;
        }
        $newContent = str_replace($search, $replace, $content);
        if ($newContent === $content) {
            echo "⚠️ Qidirilgan matn topilmadi: {$search}\n";
            return false;
        }
        return self::putFile($remotePath, $newContent);
    }

    public static function command(string $command): bool
    {
        $res = self::request('POST', '/servers/' . self::SERVER_ID . '/command', [
            'command' => $command,
        ]);
        if ($res['code'] === 204) {
            echo "✅ Buyruq server konsoliga yuborildi: {$command}\n";
            return true;
        }
        echo "❌ Buyruq yuborilmadi (HTTP {$res['code']})\n{$res['body']}\n";
        return false;
    }

    public static function deleteFiles(string $root, array $files): bool
    {
        $res = self::request('POST', '/servers/' . self::SERVER_ID . '/files/delete', [
            'root'  => $root,
            'files' => $files,
        ]);
        if ($res['code'] === 204) {
            echo "✅ O'chirildi: " . implode(', ', $files) . "\n";
            return true;
        }
        echo "❌ O'chirib bo'lmadi (HTTP {$res['code']})\n{$res['body']}\n";
        return false;
    }
}

// CLI Command Router
$action = $argv[1] ?? 'status';

switch ($action) {
    case 'setup-a':
        EliteHost::setupOptionA();
        break;

    case 'schedules':
        EliteHost::listSchedules();
        break;

    case 'status':
        EliteHost::status();
        break;

    case 'restart':
        EliteHost::restart();
        break;

    case 'cmd':
    case 'command':
        $cmd = $argv[2] ?? null;
        if (!$cmd) {
            die("Foydalanish: php scripts/elitehost.php cmd <command>\n");
        }
        EliteHost::command($cmd);
        break;

    case 'list':
        EliteHost::listFiles($argv[2] ?? '/');
        break;

    case 'get':
        $path = $argv[2] ?? null;
        if (!$path) {
            die("Iltimos, fayl yo'lini kiriting. Masalan: php scripts/elitehost.php get .env\n");
        }
        echo EliteHost::getFile($path) . "\n";
        break;

    case 'put':
        $path = $argv[2] ?? null;
        $source = $argv[3] ?? null;
        if (!$path || !$source) {
            die("Foydalanish: php scripts/elitehost.php put <remote_path> <local_file_or_text>\n");
        }
        $content = file_exists($source) ? file_get_contents($source) : $source;
        EliteHost::putFile($path, $content);
        break;

    case 'sync':
        $path = $argv[2] ?? null;
        if (!$path) {
            die("Foydalanish: php scripts/elitehost.php sync <relative_file_path>\n");
        }
        EliteHost::syncFile($path);
        break;

    case 'del':
        $path = $argv[2] ?? null;
        if (!$path) {
            die("Foydalanish: php scripts/elitehost.php del <file>\n");
        }
        $dir = dirname($path);
        if ($dir === '.') $dir = '/';
        $file = basename($path);
        EliteHost::deleteFiles($dir, [$file]);
        break;

    case 'replace':
        $path = $argv[2] ?? null;
        $search = $argv[3] ?? null;
        $replace = $argv[4] ?? null;
        if (!$path || !$search || $replace === null) {
            die("Foydalanish: php scripts/elitehost.php replace <path> <search> <replace>\n");
        }
        EliteHost::replaceInFile($path, $search, $replace);
        break;

    default:
        echo "Noma'lum buyruq: {$action}\n";
        echo "Mavjud buyruqlar: status, restart, list, get, put, del\n";
        break;
}
