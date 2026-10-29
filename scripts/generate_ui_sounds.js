// scripts/generate_ui_sounds.js
// Generates high quality 44.1kHz 16-bit PCM WAV audio files for Kitobxon UI sounds.
// Documented and inspired by Kenney Interface Sounds (CC0 1.0 Universal).

import fs from 'fs';
import path from 'path';

const SAMPLE_RATE = 44100;
const OUTPUT_DIR = path.resolve('public/sounds/ui');

if (!fs.existsSync(OUTPUT_DIR)) {
    fs.mkdirSync(OUTPUT_DIR, { recursive: true });
}

function createWavBuffer(samples) {
    const numSamples = samples.length;
    const subChunk2Size = numSamples * 2; // 16-bit mono = 2 bytes per sample
    const buffer = Buffer.alloc(44 + subChunk2Size);

    // RIFF header
    buffer.write('RIFF', 0);
    buffer.writeUInt32LE(36 + subChunk2Size, 4);
    buffer.write('WAVE', 8);

    // fmt subchunk
    buffer.write('fmt ', 12);
    buffer.writeUInt32LE(16, 16);          // Subchunk1Size (16 for PCM)
    buffer.writeUInt16LE(1, 20);           // AudioFormat (1 = PCM)
    buffer.writeUInt16LE(1, 22);           // NumChannels (1 = Mono)
    buffer.writeUInt32LE(SAMPLE_RATE, 24); // SampleRate
    buffer.writeUInt32LE(SAMPLE_RATE * 2, 28); // ByteRate (SampleRate * NumChannels * BitsPerSample/8)
    buffer.writeUInt16LE(2, 32);           // BlockAlign (NumChannels * BitsPerSample/8)
    buffer.writeUInt16LE(16, 34);          // BitsPerSample (16 bits)

    // data subchunk
    buffer.write('data', 36);
    buffer.writeUInt32LE(subChunk2Size, 40);

    for (let i = 0; i < numSamples; i++) {
        // Clamp to [-1.0, 1.0] and scale to 16-bit signed integer
        const s = Math.max(-1, Math.min(1, samples[i]));
        const intSample = s < 0 ? Math.floor(s * 0x8000) : Math.floor(s * 0x7FFF);
        buffer.writeInt16LE(intSample, 44 + i * 2);
    }

    return buffer;
}

// 1. bookmark.wav (short soft wooden tap / pop ~90ms)
function generateBookmark() {
    const duration = 0.095;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        // Frequency bends from 520Hz down to 210Hz
        const freq = 520 - (520 - 210) * Math.pow(t / duration, 0.6);
        const phase = 2 * Math.PI * freq * t;
        
        // Soft wooden acoustic timbre: 85% sine, 15% 2nd harmonic
        const raw = 0.85 * Math.sin(phase) + 0.15 * Math.sin(2 * phase);

        // Gentle envelope: 3ms attack, exponential decay
        let env = 0;
        if (t < 0.003) {
            env = t / 0.003;
        } else {
            env = Math.exp(-(t - 0.003) / 0.024);
        }
        samples[i] = raw * env * 0.45;
    }
    return samples;
}

// 2. settings_saved.wav (two-tone warm marimba confirmation ~280ms)
function generateSettingsSaved() {
    const duration = 0.30;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    const note1Freq = 659.25; // E5
    const note2Freq = 880.00; // A5
    const note2Start = 0.11;

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        let s = 0;

        // Note 1 (E5)
        if (t >= 0 && t < 0.22) {
            const dt = t;
            const phase = 2 * Math.PI * note1Freq * dt;
            const wave = 0.8 * Math.sin(phase) + 0.2 * Math.sin(2 * phase);
            const env = dt < 0.005 ? (dt / 0.005) : Math.exp(-(dt - 0.005) / 0.045);
            s += wave * env * 0.35;
        }

        // Note 2 (A5)
        if (t >= note2Start) {
            const dt = t - note2Start;
            const phase = 2 * Math.PI * note2Freq * dt;
            const wave = 0.85 * Math.sin(phase) + 0.15 * Math.sin(2 * phase);
            const env = dt < 0.005 ? (dt / 0.005) : Math.exp(-(dt - 0.005) / 0.065);
            s += wave * env * 0.40;
        }

        samples[i] = s;
    }
    return samples;
}

// 3. quiz_correct.wav (pleasant ascending chime C5 -> E5 -> G5 ~260ms)
function generateQuizCorrect() {
    const duration = 0.28;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    const notes = [
        { freq: 523.25, start: 0.00, decay: 0.06 }, // C5
        { freq: 659.25, start: 0.07, decay: 0.07 }, // E5
        { freq: 783.99, start: 0.14, decay: 0.10 }, // G5
    ];

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        let s = 0;

        for (const n of notes) {
            if (t >= n.start) {
                const dt = t - n.start;
                const phase = 2 * Math.PI * n.freq * dt;
                const wave = 0.88 * Math.sin(phase) + 0.12 * Math.sin(2 * phase);
                const env = dt < 0.004 ? (dt / 0.004) : Math.exp(-(dt - 0.004) / n.decay);
                s += wave * env * 0.32;
            }
        }
        samples[i] = s;
    }
    return samples;
}

// 4. quiz_wrong.wav (polite soft low tone ~220ms, non-annoying warm wood)
function generateQuizWrong() {
    const duration = 0.22;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        // Descending gentle low tone 155Hz -> 125Hz
        const freq = 155 - (155 - 125) * (t / duration);
        const phase1 = 2 * Math.PI * freq * t;
        const phase2 = 2 * Math.PI * (freq * 0.75) * t;
        const wave = 0.7 * Math.sin(phase1) + 0.3 * Math.sin(phase2);

        // Soft attack (8ms) so there's zero click, calm decay
        const env = t < 0.008 ? (t / 0.008) : Math.exp(-(t - 0.008) / 0.07);
        samples[i] = wave * env * 0.40;
    }
    return samples;
}

// 5. quiz_finish.wav (closing gentle chord G4-C5-E5 ~420ms)
function generateQuizFinish() {
    const duration = 0.42;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    const freqs = [392.00, 523.25, 659.25]; // G4, C5, E5
    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        let s = 0;
        for (const f of freqs) {
            const phase = 2 * Math.PI * f * t;
            const wave = Math.sin(phase);
            const env = t < 0.008 ? (t / 0.008) : Math.exp(-(t - 0.008) / 0.12);
            s += wave * env * 0.22;
        }
        samples[i] = s;
    }
    return samples;
}

// 6. achievement.wav (~950ms celestial harp arpeggio: C5 -> G5 -> C6 -> E6)
function generateAchievement() {
    const duration = 0.95;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    const notes = [
        { freq: 523.25, start: 0.00, decay: 0.16 },  // C5
        { freq: 783.99, start: 0.12, decay: 0.20 },  // G5
        { freq: 1046.50, start: 0.24, decay: 0.28 }, // C6
        { freq: 1318.51, start: 0.36, decay: 0.38 }, // E6
    ];

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        let s = 0;
        for (const n of notes) {
            if (t >= n.start) {
                const dt = t - n.start;
                const phase = 2 * Math.PI * n.freq * dt;
                // Soft chime with shimmering harmonic
                const wave = 0.85 * Math.sin(phase) + 0.15 * Math.sin(2 * phase);
                const env = dt < 0.006 ? (dt / 0.006) : Math.exp(-(dt - 0.006) / n.decay);
                s += wave * env * 0.26;
            }
        }
        samples[i] = s;
    }
    return samples;
}

// 7. purchase.wav (light coin chime ~300ms)
function generatePurchase() {
    const duration = 0.32;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    const notes = [
        { freq: 987.77, start: 0.00, decay: 0.08 },  // B5
        { freq: 1318.51, start: 0.08, decay: 0.14 }, // E6
    ];

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        let s = 0;
        for (const n of notes) {
            if (t >= n.start) {
                const dt = t - n.start;
                const phase = 2 * Math.PI * n.freq * dt;
                // High crystalline coin ring
                const wave = 0.80 * Math.sin(phase) + 0.20 * Math.sin(3 * phase);
                const env = dt < 0.003 ? (dt / 0.003) : Math.exp(-(dt - 0.003) / n.decay);
                s += wave * env * 0.24;
            }
        }
        samples[i] = s;
    }
    return samples;
}

// 8. chat_message.wav (soft bubble ping ~160ms)
function generateChatMessage() {
    const duration = 0.16;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        // Pitch upward bend then downward settle
        let freq;
        if (t < 0.04) {
            freq = 620 + (880 - 620) * (t / 0.04);
        } else {
            freq = 880 - (880 - 740) * ((t - 0.04) / (duration - 0.04));
        }
        const phase = 2 * Math.PI * freq * t;
        const wave = Math.sin(phase);

        const env = t < 0.005 ? (t / 0.005) : Math.exp(-(t - 0.005) / 0.045);
        samples[i] = wave * env * 0.38;
    }
    return samples;
}

// 9. error.wav (muted calm warning ~240ms)
function generateError() {
    const duration = 0.25;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    const notes = [
        { freq: 220.00, start: 0.00, decay: 0.06 }, // A3
        { freq: 196.00, start: 0.09, decay: 0.09 }, // G3
    ];

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        let s = 0;
        for (const n of notes) {
            if (t >= n.start) {
                const dt = t - n.start;
                const phase = 2 * Math.PI * n.freq * dt;
                const wave = 0.85 * Math.sin(phase) + 0.15 * Math.sin(2 * phase);
                const env = dt < 0.006 ? (dt / 0.006) : Math.exp(-(dt - 0.006) / n.decay);
                s += wave * env * 0.36;
            }
        }
        samples[i] = s;
    }
    return samples;
}

// 10. level_up.wav (one-time majestic celebration fanfare ~950ms)
function generateLevelUp() {
    const duration = 0.95;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    const notes = [
        { freq: 523.25, start: 0.00, decay: 0.35, gain: 0.28 }, // C5
        { freq: 659.25, start: 0.10, decay: 0.40, gain: 0.30 }, // E5
        { freq: 783.99, start: 0.20, decay: 0.48, gain: 0.32 }, // G5
        { freq: 1046.50, start: 0.32, decay: 0.58, gain: 0.34 },// C6
        { freq: 1318.51, start: 0.45, decay: 0.65, gain: 0.36 } // E6
    ];

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        let s = 0;
        for (const n of notes) {
            if (t >= n.start) {
                const dt = t - n.start;
                const phase = 2 * Math.PI * n.freq * dt;
                const wave = 0.88 * Math.sin(phase) + 0.12 * Math.sin(2 * phase);
                const env = dt < 0.008 ? (dt / 0.008) : Math.exp(-(dt - 0.008) / n.decay);
                s += wave * env * n.gain;
            }
        }
        samples[i] = s;
    }
    return samples;
}

// 11. daily_bonus.wav (joyful congratulatory marimba burst ~420ms)
function generateDailyBonus() {
    const duration = 0.42;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    const notes = [
        { freq: 783.99, start: 0.00, decay: 0.12, gain: 0.32 }, // G5
        { freq: 987.77, start: 0.06, decay: 0.14, gain: 0.34 }, // B5
        { freq: 1174.66, start: 0.12, decay: 0.18, gain: 0.36 },// D6
        { freq: 1567.98, start: 0.18, decay: 0.24, gain: 0.38 } // G6
    ];

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        let s = 0;
        for (const n of notes) {
            if (t >= n.start) {
                const dt = t - n.start;
                const phase = 2 * Math.PI * n.freq * dt;
                const wave = 0.90 * Math.sin(phase) + 0.10 * Math.sin(2 * phase);
                const env = dt < 0.004 ? (dt / 0.004) : Math.exp(-(dt - 0.004) / n.decay);
                s += wave * env * n.gain;
            }
        }
        samples[i] = s;
    }
    return samples;
}

// 12. note_saved.wav (soft bookmark-like double tap with acoustic warmth ~140ms)
function generateNoteSaved() {
    const duration = 0.14;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        let s = 0;

        // Tap 1: 480Hz -> 300Hz (~65ms)
        if (t < 0.065) {
            const dt = t;
            const freq = 480 - (480 - 300) * (dt / 0.065);
            const phase = 2 * Math.PI * freq * dt;
            const wave = 0.86 * Math.sin(phase) + 0.14 * Math.sin(2 * phase);
            const env = dt < 0.003 ? (dt / 0.003) : Math.exp(-(dt - 0.003) / 0.020);
            s += wave * env * 0.40;
        }

        // Tap 2: 660Hz -> 420Hz (~80ms, starts at 0.055s)
        if (t >= 0.055) {
            const dt = t - 0.055;
            const freq = 660 - (660 - 420) * (dt / 0.080);
            const phase = 2 * Math.PI * freq * dt;
            const wave = 0.88 * Math.sin(phase) + 0.12 * Math.sin(2 * phase);
            const env = dt < 0.003 ? (dt / 0.003) : Math.exp(-(dt - 0.003) / 0.025);
            s += wave * env * 0.42;
        }

        samples[i] = s;
    }
    return samples;
}

// 13. group_message.wav (soft ambient harmonic ping for open group messages ~180ms)
function generateGroupMessage() {
    const duration = 0.18;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        const f1 = 587.33 + 60 * (t / duration);
        const f2 = 880.00 + 90 * (t / duration);
        const phase1 = 2 * Math.PI * f1 * t;
        const phase2 = 2 * Math.PI * f2 * t;
        const wave = 0.65 * Math.sin(phase1) + 0.35 * Math.sin(phase2);
        const env = t < 0.006 ? (t / 0.006) : Math.exp(-(t - 0.006) / 0.050);
        samples[i] = wave * env * 0.34;
    }
    return samples;
}

// 14. theme_light.wav (warm rising chime / sunbeam glow ~160ms)
function generateThemeLight() {
    const duration = 0.16;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    const f1 = 587.33; // D5
    const f2 = 783.99; // G5
    const t2 = 0.050;

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        let s = 0;

        // Note 1 (D5)
        if (t < 0.12) {
            const phase = 2 * Math.PI * f1 * t;
            const wave = 0.85 * Math.sin(phase) + 0.15 * Math.sin(2 * phase);
            const env = t < 0.004 ? (t / 0.004) : Math.exp(-(t - 0.004) / 0.035);
            s += wave * env * 0.32;
        }

        // Note 2 (G5)
        if (t >= t2) {
            const dt = t - t2;
            const phase = 2 * Math.PI * f2 * dt;
            const wave = 0.80 * Math.sin(phase) + 0.20 * Math.sin(2 * phase);
            const env = dt < 0.005 ? (dt / 0.005) : Math.exp(-(dt - 0.005) / 0.045);
            s += wave * env * 0.36;
        }

        samples[i] = s;
    }
    return samples;
}

// 15. theme_dark.wav (calm descending twilight chime ~180ms)
function generateThemeDark() {
    const duration = 0.18;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    const f1 = 783.99; // G5
    const f2 = 587.33; // D5
    const t2 = 0.050;

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        let s = 0;

        // Note 1 (G5)
        if (t < 0.12) {
            const phase = 2 * Math.PI * f1 * t;
            const wave = 0.88 * Math.sin(phase) + 0.12 * Math.sin(2 * phase);
            const env = t < 0.005 ? (t / 0.005) : Math.exp(-(t - 0.005) / 0.035);
            s += wave * env * 0.30;
        }

        // Note 2 (D5)
        if (t >= t2) {
            const dt = t - t2;
            const phase = 2 * Math.PI * f2 * dt;
            const wave = 0.92 * Math.sin(phase) + 0.08 * Math.sin(2 * phase);
            const env = dt < 0.006 ? (dt / 0.006) : Math.exp(-(dt - 0.006) / 0.055);
            s += wave * env * 0.34;
        }

        samples[i] = s;
    }
    return samples;
}

// 16. language_switch.wav (organic tactile page-turn tap ~120ms)
function generateLanguageSwitch() {
    const duration = 0.12;
    const numSamples = Math.floor(SAMPLE_RATE * duration);
    const samples = new Float32Array(numSamples);

    const t2 = 0.040;

    for (let i = 0; i < numSamples; i++) {
        const t = i / SAMPLE_RATE;
        let s = 0;

        // Tap 1
        if (t < 0.07) {
            const freq = 480 - (480 - 320) * (t / 0.07);
            const phase = 2 * Math.PI * freq * t;
            const wave = 0.85 * Math.sin(phase) + 0.15 * Math.sin(2 * phase);
            const env = t < 0.003 ? (t / 0.003) : Math.exp(-(t - 0.003) / 0.018);
            s += wave * env * 0.35;
        }

        // Tap 2
        if (t >= t2) {
            const dt = t - t2;
            const freq = 620 - (620 - 440) * (dt / 0.07);
            const phase = 2 * Math.PI * freq * dt;
            const wave = 0.88 * Math.sin(phase) + 0.12 * Math.sin(2 * phase);
            const env = dt < 0.003 ? (dt / 0.003) : Math.exp(-(dt - 0.003) / 0.022);
            s += wave * env * 0.38;
        }

        samples[i] = s;
    }
    return samples;
}

const generators = {
    'bookmark.wav': generateBookmark,
    'settings_saved.wav': generateSettingsSaved,
    'quiz_correct.wav': generateQuizCorrect,
    'quiz_wrong.wav': generateQuizWrong,
    'quiz_finish.wav': generateQuizFinish,
    'achievement.wav': generateAchievement,
    'level_up.wav': generateLevelUp,
    'daily_bonus.wav': generateDailyBonus,
    'note_saved.wav': generateNoteSaved,
    'group_message.wav': generateGroupMessage,
    'theme_light.wav': generateThemeLight,
    'theme_dark.wav': generateThemeDark,
    'language_switch.wav': generateLanguageSwitch,
    'purchase.wav': generatePurchase,
    'chat_message.wav': generateChatMessage,
    'error.wav': generateError,
};

for (const [filename, gen] of Object.entries(generators)) {
    const samples = gen();
    const buffer = createWavBuffer(samples);
    const outPath = path.join(OUTPUT_DIR, filename);
    fs.writeFileSync(outPath, buffer);
    console.log(`Generated: ${filename} (${buffer.length} bytes, ${(samples.length / SAMPLE_RATE * 1000).toFixed(0)}ms)`);
}

console.log('All UI sound assets generated successfully!');
