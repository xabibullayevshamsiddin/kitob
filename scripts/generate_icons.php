<?php
$sourcePath = __DIR__ . '/../public/storage/logo/photo_2026-10-26_14-48-15.jpg';
if (!file_exists($sourcePath)) {
    die("Source file not found\n");
}

$srcImg = imagecreatefromjpeg($sourcePath);
$srcW = imagesx($srcImg);
$srcH = imagesy($srcImg);

$targets = [
    __DIR__ . '/../public/icons/kitobxon-512.png' => [512, 512],
    __DIR__ . '/../public/icons/kitobxon-192.png' => [192, 192],
    __DIR__ . '/../public/icons/apple-touch-icon.png' => [180, 180],
    __DIR__ . '/../public/favicon-48x48.png' => [48, 48],
    __DIR__ . '/../public/favicon-32x32.png' => [32, 32],
    __DIR__ . '/../public/favicon-16x16.png' => [16, 16],
    __DIR__ . '/../public/images/logo.png' => [512, 512],
];

foreach ($targets as $destPath => [$w, $h]) {
    $destImg = imagecreatetruecolor($w, $h);
    // Fill white background or keep aspect ratio
    $white = imagecolorallocate($destImg, 255, 255, 255);
    imagefilledrectangle($destImg, 0, 0, $w, $h, $white);
    
    // Fit source into destination preserving aspect ratio
    $scale = min($w / $srcW, $h / $srcH);
    $newW = (int) round($srcW * $scale);
    $newH = (int) round($srcH * $scale);
    $dstX = (int) round(($w - $newW) / 2);
    $dstY = (int) round(($h - $newH) / 2);

    imagecopyresampled($destImg, $srcImg, $dstX, $dstY, 0, 0, $newW, $newH, $srcW, $srcH);
    
    // Save PNG
    imagepng($destImg, $destPath, 9);
    imagedestroy($destImg);
    echo "Generated: " . basename($destPath) . " ({$w}x{$h})\n";
}

// Copy favicon.ico from 32x32 png
copy(__DIR__ . '/../public/favicon-32x32.png', __DIR__ . '/../public/favicon.ico');
echo "Updated favicon.ico\n";

imagedestroy($srcImg);
echo "All icons generated successfully!\n";
