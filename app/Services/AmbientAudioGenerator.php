<?php

namespace App\Services;

class AmbientAudioGenerator
{
    /**
     * Create a standard 16-bit PCM WAV binary string.
     *
     * @param array $samples Array of float values between -1.0 and 1.0
     * @param int $sampleRate
     * @return string
     */
    public static function createWav(array $samples, int $sampleRate = 22050): string
    {
        $numSamples = count($samples);
        $numChannels = 1;
        $bitsPerSample = 16;
        $byteRate = $sampleRate * $numChannels * ($bitsPerSample / 8);
        $blockAlign = $numChannels * ($bitsPerSample / 8);
        $dataSize = $numSamples * 2;

        // RIFF header
        $header = 'RIFF';
        $header .= pack('V', 36 + $dataSize);
        $header .= 'WAVE';
        
        // fmt chunk
        $header .= 'fmt ';
        $header .= pack('V', 16); // Subchunk1Size (16 for PCM)
        $header .= pack('v', 1);  // AudioFormat (1 for PCM)
        $header .= pack('v', $numChannels);
        $header .= pack('V', $sampleRate);
        $header .= pack('V', $byteRate);
        $header .= pack('v', $blockAlign);
        $header .= pack('v', $bitsPerSample);

        // data chunk
        $header .= 'data';
        $header .= pack('V', $dataSize);

        $body = '';
        foreach ($samples as $sample) {
            $clamped = max(-1.0, min(1.0, $sample));
            $intSample = (int) ($clamped * 32767);
            $body .= pack('v', $intSample);
        }

        return $header . $body;
    }

    /**
     * Generate Rain ambient sound (soft pink noise with envelope).
     */
    public static function generateRain(int $seconds = 8, int $sampleRate = 22050): string
    {
        $totalSamples = $seconds * $sampleRate;
        $samples = [];
        $b0 = $b1 = $b2 = 0.0;

        for ($i = 0; $i < $totalSamples; $i++) {
            $white = ((mt_rand() / mt_getrandmax()) * 2.0) - 1.0;
            // Pink noise approximation
            $b0 = 0.99765 * $b0 + $white * 0.0990460;
            $b1 = 0.96300 * $b1 + $white * 0.2965164;
            $b2 = 0.57000 * $b2 + $white * 1.0526913;
            $pink = ($b0 + $b1 + $b2 + $white * 0.1848) * 0.12;

            // Soft drop variation
            $drop = sin($i * 0.005) * 0.05 * sin($i * 0.013);
            $samples[] = ($pink + $drop) * 0.7;
        }

        // Apply smooth loop envelope at start & end
        self::applyLoopFade($samples, $sampleRate);

        return self::createWav($samples, $sampleRate);
    }

    /**
     * Generate Calm Piano / Rhodes warm ambient chords.
     */
    public static function generatePiano(int $seconds = 10, int $sampleRate = 22050): string
    {
        $totalSamples = $seconds * $sampleRate;
        $samples = array_fill(0, $totalSamples, 0.0);

        // Chords frequencies (Cmaj7 -> Am9 -> Fmaj7)
        $chords = [
            ['time' => 0.0, 'notes' => [261.63, 329.63, 392.00, 493.88]], // C4, E4, G4, B4
            ['time' => 3.3, 'notes' => [220.00, 261.63, 329.63, 392.00, 493.88]], // A3, C4, E4, G4, B4
            ['time' => 6.6, 'notes' => [174.61, 220.00, 261.63, 329.63]], // F3, A3, C4, E4
        ];

        foreach ($chords as $chord) {
            $startSample = (int) ($chord['time'] * $sampleRate);
            $chordDurationSamples = (int) (3.5 * $sampleRate);

            for ($n = 0; $n < count($chord['notes']); $n++) {
                $freq = $chord['notes'][$n];
                $noteAmp = 0.15 / count($chord['notes']);

                for ($s = 0; $s < $chordDurationSamples; $s++) {
                    $idx = $startSample + $s;
                    if ($idx >= $totalSamples) {
                        $idx = $idx % $totalSamples; // wrap for loop continuity
                    }

                    $t = $s / $sampleRate;
                    // Bell / warm piano envelope (quick attack, gentle exponential decay)
                    $env = exp(-$t * 0.85);

                    // Sine + warm second harmonic
                    $wave = sin(2 * M_PI * $freq * $t) * 0.75 + sin(4 * M_PI * $freq * $t) * 0.25;

                    $samples[$idx] += $wave * $env * $noteAmp;
                }
            }
        }

        self::applyLoopFade($samples, $sampleRate);

        return self::createWav($samples, $sampleRate);
    }

    /**
     * Generate Forest Breeze & Birds ambient track.
     */
    public static function generateForest(int $seconds = 8, int $sampleRate = 22050): string
    {
        $totalSamples = $seconds * $sampleRate;
        $samples = [];
        $filter = 0.0;

        for ($i = 0; $i < $totalSamples; $i++) {
            $white = ((mt_rand() / mt_getrandmax()) * 2.0) - 1.0;
            // Soft wind breeze (lowpass filtered white noise)
            $filter = $filter + 0.05 * ($white - $filter);
            $breeze = $filter * (0.15 + 0.05 * sin($i * 0.0008));

            // Soft bird chirps at specific intervals
            $chirp = 0.0;
            $t = $i / $sampleRate;
            if (($t > 1.2 && $t < 1.6) || ($t > 4.5 && $t < 4.9)) {
                $chirpT = $t - ($t > 3 ? 4.5 : 1.2);
                $chirpFreq = 2400 + 400 * sin($chirpT * 40);
                $chirpEnv = sin($chirpT * M_PI / 0.4);
                $chirp = sin(2 * M_PI * $chirpFreq * $chirpT) * $chirpEnv * 0.08;
            }

            $samples[] = ($breeze + $chirp) * 0.6;
        }

        self::applyLoopFade($samples, $sampleRate);

        return self::createWav($samples, $sampleRate);
    }

    /**
     * Smoothly crossfade start and end to make perfect seamless audio loop.
     */
    protected static function applyLoopFade(array &$samples, int $sampleRate, float $fadeDuration = 0.2): void
    {
        $fadeSamples = (int) ($fadeDuration * $sampleRate);
        $count = count($samples);

        for ($i = 0; $i < $fadeSamples && $i < $count; $i++) {
            $factor = $i / $fadeSamples;
            $samples[$i] *= $factor;
            $samples[$count - 1 - $i] *= $factor;
        }
    }
}
