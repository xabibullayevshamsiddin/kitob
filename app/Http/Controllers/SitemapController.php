<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public const CACHE_KEY = 'kitobxon_sitemap_xml';
    public const CACHE_TTL = 86400; // 24 hours

    /**
     * Generate dynamic sitemap.xml for search engines (Google, Yandex, Bing).
     */
    public function index(Request $request): Response
    {
        // Allow manual cache purge via ?refresh=1
        if ($request->has('refresh')) {
            Cache::forget(self::CACHE_KEY);
        }

        $xml = Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            return $this->buildSitemapXml();
        });

        return response($xml, 200, [
            'Content-Type' => 'application/xml; charset=UTF-8',
            'X-Robots-Tag' => 'noindex, follow',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    /**
     * Clear the cached sitemap XML.
     */
    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Construct the valid XML string.
     */
    protected function buildSitemapXml(): string
    {
        $baseUrl = rtrim(config('app.url', 'https://kitobxon.elitehost.uz'), '/');
        // Ensure production URLs always use HTTPS
        if (str_starts_with($baseUrl, 'http://kitobxon.elitehost.uz')) {
            $baseUrl = str_replace('http://', 'https://', $baseUrl);
        }

        $books = Book::where('is_active', true)
            ->select(['id', 'title', 'slug', 'author', 'genre', 'cover_image', 'updated_at', 'created_at'])
            ->latest('updated_at')
            ->get();

        $genres = Book::where('is_active', true)
            ->whereNotNull('genre')
            ->where('genre', '!=', '')
            ->distinct()
            ->pluck('genre')
            ->filter();

        $now = now()->toAtomString();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" ' . "\n";
        $xml .= '        xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" ' . "\n";
        $xml .= '        xmlns:image="http://www.google.com/schemas/sitemap-image/1.1" ' . "\n";
        $xml .= '        xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">' . "\n";

        // 1. Core Public Landing Pages
        $staticPages = [
            [
                'loc'        => $baseUrl . '/',
                'lastmod'    => $now,
                'changefreq' => 'daily',
                'priority'   => '1.0',
            ],
            [
                'loc'        => $baseUrl . '/catalog',
                'lastmod'    => $now,
                'changefreq' => 'daily',
                'priority'   => '0.9',
            ],
            [
                'loc'        => $baseUrl . '/leaderboard',
                'lastmod'    => $now,
                'changefreq' => 'daily',
                'priority'   => '0.8',
            ],
            [
                'loc'        => $baseUrl . '/about',
                'lastmod'    => $now,
                'changefreq' => 'monthly',
                'priority'   => '0.6',
            ],
            [
                'loc'        => $baseUrl . '/contact',
                'lastmod'    => $now,
                'changefreq' => 'monthly',
                'priority'   => '0.6',
            ],
            [
                'loc'        => $baseUrl . '/faq',
                'lastmod'    => $now,
                'changefreq' => 'monthly',
                'priority'   => '0.5',
            ],
        ];

        foreach ($staticPages as $page) {
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($page['loc'], ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $page['lastmod'] . "</lastmod>\n";
            $xml .= "    <changefreq>" . $page['changefreq'] . "</changefreq>\n";
            $xml .= "    <priority>" . $page['priority'] . "</priority>\n";
            $xml .= "  </url>\n";
        }

        // 2. Genre / Category Directory Pages
        foreach ($genres as $genre) {
            $genreUrl = $baseUrl . '/catalog?genre=' . urlencode($genre);
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($genreUrl, ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $now . "</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.7</priority>\n";
            $xml .= "  </url>\n";
        }

        // 3. Public Book Pages with Google Image sitemap tags
        foreach ($books as $book) {
            $bookUrl = $baseUrl . '/books/' . rawurlencode($book->slug);
            $lastmod = ($book->updated_at ?? $book->created_at ?? now())->toAtomString();

            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($bookUrl, ENT_XML1, 'UTF-8') . "</loc>\n";
            $xml .= "    <lastmod>" . $lastmod . "</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.9</priority>\n";

            // Add cover image for Google Images rich indexation (skip placeholders)
            $coverUrl = $book->cover_url;
            if ($coverUrl && !str_contains($coverUrl, 'book-placeholder')) {
                // Ensure cover image URL is absolute and uses production domain
                if (!str_starts_with($coverUrl, 'http://') && !str_starts_with($coverUrl, 'https://')) {
                    $coverUrl = $baseUrl . '/' . ltrim($coverUrl, '/');
                } elseif (str_starts_with($coverUrl, 'http://kitobxon.elitehost.uz')) {
                    $coverUrl = str_replace('http://', 'https://', $coverUrl);
                }

                $caption = trim(($book->title ?? '') . ' — ' . ($book->author ?? 'Kitobxon'));

                $xml .= "    <image:image>\n";
                $xml .= "      <image:loc>" . htmlspecialchars($coverUrl, ENT_XML1, 'UTF-8') . "</image:loc>\n";
                $xml .= "      <image:title>" . htmlspecialchars($book->title ?? '', ENT_XML1, 'UTF-8') . "</image:title>\n";
                $xml .= "      <image:caption>" . htmlspecialchars($caption, ENT_XML1, 'UTF-8') . "</image:caption>\n";
                $xml .= "    </image:image>\n";
            }

            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }
}
