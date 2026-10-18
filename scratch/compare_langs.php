<?php

function flatten(array $array, string $prefix = ''): array {
    $result = [];
    foreach ($array as $key => $value) {
        $fullKey = $prefix === '' ? $key : "{$prefix}.{$key}";
        if (is_array($value)) {
            $result += flatten($value, $fullKey);
        } else {
            $result[$fullKey] = $value;
        }
    }
    return $result;
}

$files = ['site', 'ai'];
foreach ($files as $file) {
    echo "=== Testing {$file}.php ===" . PHP_EOL;
    $uz = flatten(require __DIR__ . "/../lang/uz/{$file}.php");
    $ru = flatten(require __DIR__ . "/../lang/ru/{$file}.php");
    $en = flatten(require __DIR__ . "/../lang/en/{$file}.php");

    echo "UZ keys count: " . count($uz) . PHP_EOL;
    echo "RU keys count: " . count($ru) . PHP_EOL;
    echo "EN keys count: " . count($en) . PHP_EOL;

    $ruMissing = array_diff_key($uz, $ru);
    $enMissing = array_diff_key($uz, $en);
    $uzMissingFromRu = array_diff_key($ru, $uz);
    $uzMissingFromEn = array_diff_key($en, $uz);

    if ($ruMissing) {
        echo "Missing in RU from UZ:\n" . implode("\n", array_keys($ruMissing)) . PHP_EOL;
    }
    if ($enMissing) {
        echo "Missing in EN from UZ:\n" . implode("\n", array_keys($enMissing)) . PHP_EOL;
    }
    if ($uzMissingFromRu) {
        echo "Missing in UZ from RU:\n" . implode("\n", array_keys($uzMissingFromRu)) . PHP_EOL;
    }
    if ($uzMissingFromEn) {
        echo "Missing in UZ from EN:\n" . implode("\n", array_keys($uzMissingFromEn)) . PHP_EOL;
    }

    if (!$ruMissing && !$enMissing && !$uzMissingFromRu && !$uzMissingFromEn) {
        echo "PERFECT 100% PARITY! (0 missing keys)\n" . PHP_EOL;
    }
}
