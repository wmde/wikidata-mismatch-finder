<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\WikibaseAPIClient;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Kevinrob\GuzzleCache\Strategy\PrivateCacheStrategy;
use Kevinrob\GuzzleCache\Storage\LaravelCacheStorage;
use Illuminate\Support\Facades\Cache;

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
