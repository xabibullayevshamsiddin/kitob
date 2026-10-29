// scripts/test_sound_system.js
// Verification of UI Sound system parameters, files, and contracts

import fs from 'fs';
import path from 'path';

console.log('--- Verifying UI Sound Assets ---');
const soundsDir = path.resolve('public/sounds/ui');
const expectedSounds = [
    'bookmark.wav',
    'settings_saved.wav',
    'quiz_correct.wav',
    'quiz_wrong.wav',
    'quiz_finish.wav',
    'achievement.wav',
    'level_up.wav',
    'daily_bonus.wav',
    'note_saved.wav',
    'group_message.wav',
    'purchase.wav',
    'chat_message.wav',
    'error.wav',
    'theme_light.wav',
    'theme_dark.wav',
    'language_switch.wav'
];

let allFilesExist = true;
for (const file of expectedSounds) {
    const filePath = path.join(soundsDir, file);
    if (!fs.existsSync(filePath)) {
        console.error(`MISSING: ${file}`);
        allFilesExist = false;
    } else {
        const stats = fs.statSync(filePath);
        console.log(`OK: ${file} (${stats.size} bytes)`);
    }
}

if (!allFilesExist) {
    console.error('FAIL: Some sound files are missing!');
    process.exit(1);
}

// Verify credits and documentation
const creditsPath = path.join(soundsDir, 'README.md');
if (fs.existsSync(creditsPath)) {
    console.log('OK: README.md / Credits documentation exists.');
} else {
    console.error('FAIL: README.md documentation is missing.');
    process.exit(1);
}

// Verify sound-manager.js exists
const managerPath = path.resolve('resources/js/sound-manager.js');
if (fs.existsSync(managerPath)) {
    const code = fs.readFileSync(managerPath, 'utf8');
    const requiredMethods = [
        'synthBookmark',
        'synthSettingsSaved',
        'synthQuizCorrect',
        'synthQuizWrong',
        'synthQuizFinish',
        'synthAchievement',
        'synthLevelUp',
        'synthDailyBonus',
        'synthNoteSaved',
        'synthGroupMessage',
        'synthPurchase',
        'synthChatMessage',
        'synthError',
        'synthThemeLight',
        'synthThemeDark',
        'synthLanguageSwitch',
        'isSuppressed',
        'isMediaPlaying',
        'isMicrophoneActive',
        'setEnabled',
        'setVolume',
        'test'
    ];

    let allMethodsPresent = true;
    for (const m of requiredMethods) {
        if (!code.includes(m)) {
            console.error(`MISSING METHOD in manager: ${m}`);
            allMethodsPresent = false;
        }
    }

    if (allMethodsPresent) {
        console.log('OK: sound-manager.js contains all required synthesis & suppression methods.');
    } else {
        process.exit(1);
    }
}

console.log('--- ALL SOUND SYSTEM FILE & CONTRACT TESTS PASSED ---');
