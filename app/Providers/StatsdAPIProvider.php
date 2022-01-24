<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\StatsdAPIClient;

/**
 * Provider for the StatsdAPIClient
 */
class StatsdAPIProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(StatsdAPIClient::class, function ($app) {

            return new StatsdAPIClient(config('wikidata.statsd.endpoint_url'), config('wikidata.statsd.namespace'));
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
