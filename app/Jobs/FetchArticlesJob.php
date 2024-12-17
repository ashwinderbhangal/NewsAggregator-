<?php
namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use App\Services\ArticleFetcher;
use Illuminate\Support\Facades\Log;

class FetchArticlesJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        try {
            Log::info('FetchArticlesJob started');
            app(ArticleFetcher::class)->fetchAndStoreArticles();
            Log::info('FetchArticlesJob completed successfully');
        } catch (\Exception $e) {
            Log::error('FetchArticlesJob failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
