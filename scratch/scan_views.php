<?php
$dir = __DIR__ . '/../resources/views';
$patterns = [
    'Obuna bo',
    'Saqlangan',
    'Tanga',
    'Do‘koni',
    'Yuklanmoqda',
    'Fikr',
    'Musiqa',
    'Varaqlash',
];

$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($dir));
foreach ($iterator as $file) {
    if ($file->isDir() || !str_ends_with($file->getFilename(), '.blade.php')) continue;
    $path = $file->getPathname();
    $content = file_get_contents($path);
    $rel = str_replace(realpath(__DIR__ . '/..') . DIRECTORY_SEPARATOR, '', $path);
    
    // Check if html lang="uz" is still in any file
    if (preg_match('/<html\s+lang=["\']uz["\']/i', $content)) {
        echo "[STATIC HTML LANG] " . $rel . PHP_EOL;
    }
}
