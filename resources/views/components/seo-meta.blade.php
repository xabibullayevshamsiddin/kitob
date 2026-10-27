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
    $siteName = (!empty($rawName) && !in_array($rawName, ['Laravel', 'Davra_books'])) ? $rawName : 'Kitobxon';
    
    // Page Title
    if ($book) {
        $authorStr = !empty($book->author) ? " — {$book->author}" : '';
        $pageTitle = "{$book->title}{$authorStr} kitobi mutolaasi | {$siteName}";
    } elseif ($title) {
        $pageTitle = "{$title} — {$siteName}";
    } else {
        $pageTitle = "{$siteName} — Kitob mutolaasi, audio darslar va rivojlanish ekotizimi";
    }

    // Page Description
    if ($book) {
        $cleanDesc = trim(strip_tags($book->description ?? ''));
        if (strlen($cleanDesc) >= 30) {
            $pageDesc = $cleanDesc;
        } else {
            $authorPart = !empty($book->author) ? "Muallif: {$book->author}. " : '';
            $genrePart = !empty($book->genre) ? "Janr: {$book->genre}. " : '';
            $pageDesc = "\"{$book->title}\" kitobini Kitobxon platformasida onlayn mutolaa qiling, audio formatida tinglang va tahliliy testlarni topshiring. {$authorPart}{$genrePart}";
        }
    } elseif ($description) {
        $pageDesc = $description;
    } else {
        $pageDesc = "Har hafta sara kitob, chuqur tahlillar, interaktiv testlar va jonli kitobxonlik jamiyati. Bepul onlayn mutolaa qiling.";
    }

    // Canonical URL
    $pageUrl = $url ?? url()->current();
    if (str_starts_with($pageUrl, 'http://kitobxon.elitehost.uz')) {
        $pageUrl = str_replace('http://', 'https://', $pageUrl);
    }

    // Cover Image
    $rawImage = $image ?? ($book ? $book->cover_url : null) ?? asset('storage/logo/photo_2026-10-26_14-48-15.jpg');
    if (!str_starts_with($rawImage, 'http://') && !str_starts_with($rawImage, 'https://')) {
        $pageImage = url($rawImage);
    } else {
        $pageImage = $rawImage;
    }
    if (str_starts_with($pageImage, 'http://kitobxon.elitehost.uz')) {
        $pageImage = str_replace('http://', 'https://', $pageImage);
    }

    $googleVerify = env('GOOGLE_SITE_VERIFICATION');
    $yandexVerify = env('YANDEX_VERIFICATION');

    $ratingsCount = ($book && method_exists($book, 'ratingsCount')) ? $book->ratingsCount() : 0;
    $averageRating = ($book && method_exists($book, 'averageRating')) ? (float) $book->averageRating() : 0.0;
@endphp

<!-- Primary Meta Tags -->
<title>{{ $pageTitle }}</title>
<meta name="title" content="{{ $pageTitle }}">
<meta name="description" content="{{ Str::limit($pageDesc, 160) }}">
<meta name="keywords" content="kitobxon, elektron kitoblar, audio kitoblar, kitob o'qish, mutolaa, kitoblar, testlar, ta'lim{{ $book ? ', ' . e($book->title) . ', ' . e($book->author ?? '') . ($book->genre ? ', ' . e($book->genre) : '') : '' }}">
<link rel="canonical" href="{{ $pageUrl }}">
<meta name="theme-color" content="#07090E">

<!-- Search Engine Robots Directives -->
<meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="googlebot" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
<meta name="yandex" content="index, follow">

@if($googleVerify)
<meta name="google-site-verification" content="{{ $googleVerify }}">
@endif
@if($yandexVerify)
<meta name="yandex-verification" content="{{ $yandexVerify }}">
@endif

<!-- Open Graph / Facebook / Telegram -->
<meta property="og:type" content="{{ $type }}">
<meta property="og:url" content="{{ $pageUrl }}">
<meta property="og:site_name" content="{{ $siteName }}">
<meta property="og:title" content="{{ $pageTitle }}">
<meta property="og:description" content="{{ Str::limit($pageDesc, 200) }}">
<meta property="og:image" content="{{ $pageImage }}">
<meta property="og:image:secure_url" content="{{ $pageImage }}">
<meta property="og:image:alt" content="{{ $pageTitle }}">
<meta property="og:locale" content="uz_UZ">

<!-- Twitter / X -->
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:url" content="{{ $pageUrl }}">
<meta name="twitter:title" content="{{ $pageTitle }}">
<meta name="twitter:description" content="{{ Str::limit($pageDesc, 200) }}">
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
    "name": @json($book->author ?? 'Kitobxon')
  },
  "description": @json(Str::limit($pageDesc, 300)),
  "image": @json($pageImage),
  "url": @json($pageUrl),
  @if($book->genre)
  "genre": @json($book->genre),
  @endif
  "inLanguage": "uz",
  "publisher": {
    "@type": "Organization",
    "name": "Kitobxon",
    "url": @json(url('/'))
  },
  "bookFormat": "https://schema.org/EBook",
  @if($ratingsCount > 0 && $averageRating > 0)
  "aggregateRating": {
    "@type": "AggregateRating",
    "ratingValue": "{{ number_format($averageRating, 1) }}",
    "reviewCount": "{{ $ratingsCount }}",
    "bestRating": "5",
    "worstRating": "1"
  },
  @endif
  "offers": {
    "@type": "Offer",
    "price": "0",
    "priceCurrency": "UZS",
    "availability": "https://schema.org/InStock"
  }
}
</script>

<!-- Breadcrumb Schema for Google Rich Snippets -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "BreadcrumbList",
  "itemListElement": [
    {
      "@type": "ListItem",
      "position": 1,
      "name": "Bosh sahifa",
      "item": @json(url('/'))
    },
    {
      "@type": "ListItem",
      "position": 2,
      "name": "Kitoblar katalogi",
      "item": @json(route('books.catalog'))
    },
    {
      "@type": "ListItem",
      "position": 3,
      "name": @json($book->title),
      "item": @json($pageUrl)
    }
  ]
}
</script>
@else
<!-- WebSite Schema with SearchAction for Sitelinks Searchbox -->
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "WebSite",
  "name": "{{ $siteName }}",
  "url": @json(url('/')),
  "potentialAction": {
    "@type": "SearchAction",
    "target": {
      "@type": "EntryPoint",
      "urlTemplate": "{{ url('/catalog') }}?search={search_term_string}"
    },
    "query-input": "required name=search_term_string"
  }
}
</script>
@endif
