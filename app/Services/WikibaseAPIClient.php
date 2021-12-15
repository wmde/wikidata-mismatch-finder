<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Exceptions\WikibaseValueParserException;
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

    public function formatEntities(array $ids, string $lang): Response
    {
        $response = $this->get('wbformatentities', [
            'ids' => implode('|', $ids),
            'uselang' => $lang
        ]);

        return $response;
    }

    public function getLabels(array $ids, string $lang): array
    {
        // wbformatentities only allows for up to 50 entities to be formatted at a time
        return collect($ids)->chunk(50)
            ->map(function ($chunk) use ($lang) {
                $response = $this->formatEntities($chunk->toArray(), $lang);
                return $response['wbformatentities'];
            })
            ->collapse()
            // The code below was added due to the fact that wbformatentities only
            // returns labels formatted as html links, however we only require the
            // label text. Therefore, we extract the text from the links.
            ->map('strip_tags')
            ->toArray();
    }
}
