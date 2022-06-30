<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WikibaseAPIClient;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Kevinrob\GuzzleCache\Storage\LaravelCacheStorage;
use Illuminate\Support\Facades\Cache;

/**
 * Provider for the Wikidata API client
 *
 * The provided WikibaseAPIClient will use the wikidata.api.url from config.
 */
class WikidataAPIProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(WikibaseAPIClient::class, function ($app) {
            $store = new LaravelCacheStorage(Cache::store('file'));
            $strategy = new PrivateCacheStrategy($store);

            return new WikibaseAPIClient(config('wikidata.api.url'), new CacheMiddleware($strategy));
        });
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
