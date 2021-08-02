<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Exceptions\WikibaseValueParserException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Arr;
use Kevinrob\GuzzleCache\CacheMiddleware;

class WikibaseAPIClient
{

    /**
     * @var string wikibase api url
     */
    private $baseUrl;

    /**
     * @var CacheMiddleware Guzzlehttp response caching middleware
     */
    private $cache;

    public function __construct(string $baseUrl, CacheMiddleware $cache)
    {
        $this->baseUrl = $baseUrl;
        $this->cache = $cache;
    }

    private function get(string $action, array $params): Response
    {
        return Http::withMiddleware($this->cache)
            ->get($this->baseUrl, array_merge([
                'action' => $action,
                'format' => 'json',
                'maxage' => config('wikidata.response_cache.ttl')
            ], $params));
    }

    public function parseValue(string $property, $value): Response
    {
        $response = $this->get('wbparsevalue', [
            'values' => $value,
            'property' => $property,
            'validate' => true
        ]);

        // Checking for an errors field in the response, since Wikibase api
        // responds with 200 even for erroneous requests
        if (isset($response['error'])) {
            throw new WikibaseValueParserException($response['error']['info']);
        }

        return $response;
    }
}
