<?php

namespace App\Services;

use App\Models\Article;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ArticleFetcher
{
    public function fetchAndStoreArticles()
    {
        try {
            $sections = $this->getSectionsFromEnv();
            $this->fetchFromNewsAPI($sections);
            $this->fetchFromGuardian($sections);
            $this->fetchFromNYT($sections);
        } catch (\Exception $e) {
            Log::error("General Fetch Error: " . $e->getMessage());
        }
    }

    // Fetch sections from environment
    private function getSectionsFromEnv()
    {
        return explode(',', env('NEWS_SECTIONS', 'general'));
    }

    // Fetch from NewsAPI
    private function fetchFromNewsAPI($sections)
    {
        try {
            foreach ($sections as $section) {
                Log::info("Fetching from NewsAPI: Category => {$section}");

                $response = Http::withOptions(['verify' => false])
                    ->timeout(10)
                    ->get('https://newsapi.org/v2/top-headlines', [
                        'apiKey' => env('NEWS_API_KEY'),
                        'category' => trim($section),
                        'country' => 'us',
                        'pageSize' => 100,
                    ]);

                if ($response->failed()) {
                    Log::error("NewsAPI Response Failed for Category: {$section}");
                    continue;
                }

                $articles = $response->json()['articles'] ?? [];
                $this->storeArticles($this->formatArticles($articles, $section), 'NewsAPI');
                Log::info("Successfully fetched NewsAPI articles for category: {$section}");
            }
        } catch (\Exception $e) {
            Log::error("NewsAPI Fetch Error: " . $e->getMessage());
        }
    }

    // Fetch from The Guardian API
    private function fetchFromGuardian($sections)
    {
        try {
            foreach ($sections as $section) {
                Log::info("Fetching from The Guardian: Section => {$section}");

                $response = Http::withOptions(['verify' => false])
                    ->timeout(10)
                    ->get('https://content.guardianapis.com/search', [
                        'api-key' => env('GUARDIAN_API_KEY'),
                        'section' => trim($section),
                        'show-fields' => 'headline,trailText,body,byline',
                        'page-size' => 100,
                    ]);

                if ($response->failed()) {
                    Log::error("Guardian API Response Failed for Section: {$section}");
                    continue;
                }

                $articles = $response->json()['response']['results'] ?? [];
                $this->storeArticles($this->formatGuardianArticles($articles, $section), 'The Guardian');
                Log::info("Successfully fetched Guardian articles for section: {$section}");
            }
        } catch (\Exception $e) {
            Log::error("The Guardian Fetch Error: " . $e->getMessage());
        }
    }

    // Fetch from New York Times API
    private function fetchFromNYT($sections)
    {
        try {
            foreach ($sections as $section) {
                Log::info("Fetching from NYT: Section => {$section}");

                $response = Http::withOptions(['verify' => false])
                    ->timeout(10)
                    ->get("https://api.nytimes.com/svc/topstories/v2/{$section}.json", [
                        'api-key' => env('NYT_API_KEY'),
                    ]);

                if ($response->failed()) {
                    Log::error("NYT Response Failed for Section: {$section}");
                    continue;
                }

                $articles = $response->json()['results'] ?? [];
                $this->storeArticles($this->formatNYTArticles($articles, $section), 'The New York Times');
                Log::info("Successfully fetched NYT articles for section: {$section}");
            }
        } catch (\Exception $e) {
            Log::error("NYT Fetch Error: " . $e->getMessage());
        }
    }

    // Format NewsAPI articles
    private function formatArticles($articles, $section)
    {
        return array_map(function ($article) use ($section) {
            return [
                'title' => $article['title'] ?? 'No Title',
                'description' => $article['description'] ?? 'No Description',
                'url' => $article['url'] ?? '',
                'source' => $article['source']['name'] ?? 'NewsAPI',
                'author' => $article['author'] ?? null,
                'published_at' => isset($article['publishedAt'])
                    ? Carbon::parse($article['publishedAt'])->toDateTimeString()
                    : null,
                'category' => $section,
                'url_to_image' => $article['urlToImage'] ?? null,
            ];
        }, $articles);
    }

    // Format Guardian articles
    private function formatGuardianArticles($articles, $section)
    {
        return array_map(function ($article) use ($section) {
            $description = $article['fields']['body'] ?? $article['fields']['trailText'] ?? 'No Description';
            return [
                'title' => $article['fields']['headline'] ?? 'No Title',
                'description' => strip_tags($description),
                'url' => $article['webUrl'] ?? '',
                'source' => 'The Guardian',
                'author' => $article['fields']['byline'] ?? null,
                'published_at' => isset($article['webPublicationDate'])
                    ? Carbon::parse($article['webPublicationDate'])->toDateTimeString()
                    : null,
                'category' => $section,
            ];
        }, $articles);
    }

    // Format NYT articles
    private function formatNYTArticles($articles, $section)
    {
        return array_map(function ($article) use ($section) {
            return [
                'title' => $article['title'] ?? 'No Title',
                'description' => $article['abstract'] ?? 'No Description',
                'url' => $article['url'] ?? '',
                'source' => 'The New York Times',
                'author' => $article['byline'] ?? null,
                'published_at' => isset($article['published_date'])
                    ? Carbon::parse($article['published_date'])->toDateTimeString()
                    : null,
                'category' => $section,
                'url_to_image' => $article['multimedia'][0]['url'] ?? null,
            ];
        }, $articles);
    }

    // Store articles in the database
    private function storeArticles($articles, $source)
    {
        foreach ($articles as $article) {
            try {
                Article::updateOrCreate(
                    ['url' => $article['url']],
                    [
                        'title' => $article['title'],
                        'description' => $article['description'],
                        'source' => $source,
                        'author' => $article['author'],
                        'published_at' => $article['published_at'],
                        'category' => $article['category'] ?? 'general',
                        'url_to_image' => $article['url_to_image'] ?? null,
                    ]
                );
            } catch (\Exception $e) {
                Log::error("Error saving article: " . $e->getMessage());
            }
        }
    }
}
