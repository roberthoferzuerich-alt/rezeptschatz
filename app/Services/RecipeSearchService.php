<?php

namespace App\Services;

use App\Models\Recipe;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;

class RecipeSearchService
{
    /**
     * Unterstützte Webseiten für Rezepte.
     */
    public const SITES = [
        'all' => 'Alle kompatiblen Webseiten',
        'rezeptschatz' => 'Eigener Rezeptschatz',
        'chefkoch' => 'Chefkoch.de',
        'bettybossi' => 'Betty Bossi',
        'swissmilk' => 'Swissmilk',
        'migusto' => 'Migusto (Migros)',
        'fooby' => 'Fooby (Coop)',
        'gutekueche' => 'GuteKüche.ch',
    ];

    /**
     * Unterstützte Sprachen.
     */
    public const LANGUAGES = [
        'de' => 'Deutsch',
        'fr' => 'Französisch',
        'it' => 'Italienisch',
        'en' => 'Englisch',
    ];

    /**
     * Sucht im eigenen Rezeptschatz (lokale Rezepte des angemeldeten Benutzers).
     */
    public function searchLocal(User $user, string $query, int $limit = 24): Collection
    {
        $term = trim($query);
        if ($term === '') {
            return new Collection();
        }

        $like = '%' . $term . '%';

        return Recipe::where('user_id', $user->id)
            ->where(function ($q) use ($like) {
                $q->where('title', 'like', $like)
                  ->orWhere('description', 'like', $like)
                  ->orWhere('ingredients', 'like', $like)
                  ->orWhere('notes', 'like', $like)
                  ->orWhereHas('categories', function ($cat) use ($like) {
                      $cat->where('name', 'like', $like);
                  })
                  ->orWhereHas('tags', function ($tag) use ($like) {
                      $tag->where('name', 'like', $like);
                  });
            })
            ->with(['categories', 'tags'])
            ->latest()
            ->take($limit)
            ->get();
    }

    /**
     * Sucht Rezepte im Internet (Chefkoch API & Partner).
     */
    public function searchWeb(string $query, string $site = 'all', int $limit = 12): array
    {
        $term = trim($query);
        if ($term === '') {
            return [];
        }

        // Wenn site = chefkoch oder all, Chefkoch API abfragen
        if ($site === 'chefkoch' || $site === 'all') {
            return $this->searchChefkoch($term, $limit);
        }

        return [];
    }

    /**
     * Ruft die öffentliche Chefkoch v2 API ab.
     */
    public function searchChefkoch(string $query, int $limit = 12): array
    {
        try {
            $options = [];
            $caPath = 'C:/laragon/etc/ssl/cacert.pem';
            if (file_exists($caPath)) {
                $options['verify'] = $caPath;
            }

            $response = Http::withHeaders([
                'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/124.0.0.0 Safari/537.36',
                'Accept' => 'application/json',
            ])
            ->withOptions($options)
            ->timeout(10)
            ->get('https://api.chefkoch.de/v2/recipes', [
                'query' => $query,
                'limit' => $limit,
            ]);

            if (!$response->successful()) {
                return [];
            }

            $data = $response->json();
            $results = [];

            foreach ($data['results'] ?? [] as $item) {
                $rec = $item['recipe'] ?? null;
                if (!$rec || empty($rec['siteUrl'])) {
                    continue;
                }

                $imageUrl = null;
                if (!empty($rec['previewImageUrlTemplate'])) {
                    $imageUrl = str_replace('<format>', 'crop-420x280', $rec['previewImageUrlTemplate']);
                }

                $difficultyText = match ((int)($rec['difficulty'] ?? 0)) {
                    1 => 'Einfach',
                    2 => 'Normal',
                    3 => 'Pfiffig',
                    default => null,
                };

                $rating = isset($rec['rating']['rating']) ? round((float)$rec['rating']['rating'], 1) : null;
                $votes = $rec['rating']['numVotes'] ?? null;

                $results[] = [
                    'id' => $rec['id'],
                    'title' => $rec['title'],
                    'subtitle' => $rec['subtitle'] ?? null,
                    'image_url' => $imageUrl,
                    'url' => $rec['siteUrl'],
                    'source' => 'Chefkoch.de',
                    'source_key' => 'chefkoch',
                    'prep_time_minutes' => $rec['preparationTime'] ?? null,
                    'difficulty' => $difficultyText,
                    'rating' => $rating,
                    'votes' => $votes,
                ];
            }

            return $results;
        } catch (\Throwable $e) {
            report($e);
            return [];
        }
    }

    /**
     * Liefert direkte Such-URLs für Schweizer & Deutsche Kochportale.
     */
    public function getExternalSearchUrl(string $site, string $query): ?string
    {
        $encoded = urlencode(trim($query));
        return match ($site) {
            'bettybossi' => "https://www.bettybossi.ch/de/Rezept/Suche?query={$encoded}",
            'swissmilk' => "https://www.swissmilk.ch/de/rezepte-kochideen/suche/?q={$encoded}",
            'migusto' => "https://migusto.migros.ch/de/suche.html?q={$encoded}",
            'fooby' => "https://fooby.ch/de/suche.html?query={$encoded}",
            'gutekueche' => "https://www.gutekueche.ch/suche?q={$encoded}",
            'chefkoch' => "https://www.chefkoch.de/rs/s0/{$encoded}/Rezepte.html",
            default => null,
        };
    }
}
