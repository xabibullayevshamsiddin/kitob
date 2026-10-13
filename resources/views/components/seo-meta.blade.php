@props([
    'title' => null,
    'description' => null,
    'image' => null,
    'url' => null,
    'type' => 'website',
    'book' => null,
])

@php
    $siteName = config('app.name', 'Kitobxon');
    $pageTitle = $title ? "{$title} — {$siteName}" : "{$siteName} — Kitob o'qish va rivojlanish ekotizimi";
    $pageDesc = $description ?? "Har hafta sara kitob, chuqur tahlillar, interaktiv testlar va jonli kitobxonlik jamiyati.";
    $pageUrl = $url ?? url()->current();
    $pageImage = $image ?? asset('icons/kitobxon-512.png');
@endphp

<!-- Primary Meta Tags -->
<title>{{ $pageTitle }}</title>
<meta name="title" content="{{ $pageTitle }}">
<meta name="description" content="{{ Str::limit(strip_tags($pageDesc), 160) }}">
<link rel="canonical" href="{{ $pageUrl }}">

<!-- Open Graph / Facebook / Telegram -->
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $pageUrl }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ Str::limit(strip_tags($pageDesc), 200) }}">
<meta property="og:image" content="{{ $pageImage }}">

<!-- Twitter / X -->
<meta property="twitter:card" content="summary_large_image">
<meta property="twitter:url" content="{{ $pageUrl }}">
<meta property="twitter:title" content="{{ $pageTitle }}">
<meta property="twitter:description" content="{{ Str::limit(strip_tags($pageDesc), 200) }}">
<meta property="twitter:image" content="{{ $pageImage }}">

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
