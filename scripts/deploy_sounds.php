<?php

require_once __DIR__ . '/elitehost.php';

$filesToDeploy = [
    'resources/js/sound-manager.js',
    'resources/js/app.js',
    'public/build/manifest.json',
    'public/build/assets/app-0372088d.js',
    'public/build/assets/app-e4ad1bae.css',
    'public/build/assets/page-flip.browser-d344e787.js',
    'public/sounds/ui/bookmark.wav',
    'public/sounds/ui/settings_saved.wav',
    'public/sounds/ui/quiz_correct.wav',
    'public/sounds/ui/quiz_wrong.wav',
    'public/sounds/ui/quiz_finish.wav',
    'public/sounds/ui/achievement.wav',
    'public/sounds/ui/purchase.wav',
    'public/sounds/ui/chat_message.wav',
    'public/sounds/ui/error.wav',
    'public/sounds/ui/README.md',
    'resources/views/components/interface-sounds.blade.php',
    'resources/views/components/toast-container.blade.php',
    'resources/views/components/ai-typing-sound.blade.php',
    'resources/views/layouts/app.blade.php',
    'resources/views/layouts/auth.blade.php',
    'resources/views/layouts/dashboard.blade.php',
    'resources/views/livewire/settings/settings-page.blade.php',
    'resources/views/livewire/quiz/take-quiz.blade.php',
    'resources/views/pages/book-detail.blade.php',
    'resources/views/pages/reader.blade.php',
    'resources/views/components/reader/pdf-annotations.blade.php',
    'app/Http/Livewire/Settings/SettingsPage.php',
    'app/Http/Livewire/Chat/GlobalChat.php',
    'app/Http/Livewire/Profile/ProfilePage.php',
    'app/Http/Livewire/Quiz/TakeQuiz.php',
    'lang/uz/site.php',
    'lang/ru/site.php',
    'lang/en/site.php',
];

echo "Deploying " . count($filesToDeploy) . " files to EliteHost...\n";

foreach ($filesToDeploy as $file) {
    $local = __DIR__ . '/../' . $file;
    if (!file_exists($local)) {
        echo "WARN: Local file not found: $local\n";
        continue;
    }
    try {
        $content = file_get_contents($local);
        EliteHost::putFile($file, $content);
        echo "Uploaded: $file\n";
    } catch (\Throwable $e) {
        echo "ERROR uploading $file: " . $e->getMessage() . "\n";
    }
}

echo "Deployment complete!\n";
