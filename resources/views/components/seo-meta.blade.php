@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'url' => null,
    'type' => 'website',
    'book' => null,
])

@php
    $rawName = config('app.name');
    $siteName = (!empty($rawName) && $rawName !== 'Laravel') ? $rawName : 'Davra_books';
    $pageTitle = $title ? "{$title} — {$siteName}" : "{$siteName} — Kitob mutolaasi va rivojlanish ekotizimi";
    $pageDesc = $description ?? "Har hafta sara kitob, chuqur tahlillar, interaktiv testlar va jonli kitobxonlik jamiyati.";
    $pageUrl = $url ?? url()->current();
    $rawImage = $image ?? asset('storage/logo/photo_2026-10-26_14-48-15.jpg');
    $pageImage = (str_starts_with($rawImage, 'http://') || str_starts_with($rawImage, 'https://')) 
        ? $rawImage 
        : url($rawImage);
@endphp

<!-- Primary Meta Tags -->
<title>{{ $pageTitle }}</title>
<meta name="title" content="{{ $pageTitle }}">
<meta name="description" content="{{ Str::limit(strip_tags($pageDesc), 160) }}">
<link rel="canonical" href="{{ $pageUrl }}">
<meta name="theme-color" content="#07090E">

<!-- Open Graph / Facebook / Telegram -->
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $pageUrl }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($pageDesc), 200) }}">
<meta property="og:image" content="{{ $pageImage }}">
<meta property="og:image:secure_url" content="{{ $pageImage }}">
<meta property="og:image:alt" content="{{ $pageTitle }}">

<!-- Twitter / X -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $pageUrl }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ Str::limit(strip_tags($pageDesc), 200) }}">
<meta name="twitter:image" content="{{ $pageImage }}">

@if($book)
<!-- Schema.org Book Structured Data for Google Search -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Book",
  "name": @json($book->title),
  "author": {
    "@type": "Person",
    "name": @json($book->author)
  },
  "description": @json(Str::limit(strip_tags($book->description ?? ''), 250)),
  "image": @json($book->cover_url ?? $pageImage),
  "url": @json($pageUrl),
  @if($book->genre)
  "genre": @json($book->genre),
  @endif
  "inLanguage": "uz"
}
</script>
@endif
