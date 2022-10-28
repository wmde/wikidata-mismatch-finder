<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Exceptions\WikibaseValueParserException;
use Kevinrob\GuzzleCache\CacheMiddleware;
use App\Exceptions\WikibaseAPIClientException;

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

    private function list(array $values): string
    {
        if (str_contains(implode($values), '|')) {
            return "\x1f" . implode("\x1f", $values);
        } else {
            return implode('|', $values);
        }
    }

    private function get(string $action, array $params): Response
    {
        $response = Http::withMiddleware($this->cache)
            ->get($this->baseUrl, array_merge([
                'action' => $action,
                'format' => 'json',
                'maxage' => config('wikidata.response_cache.ttl')
            ], $params));

        // Checking for an errors field in the response, since Wikibase api
        // responds with 200 even for erroneous requests
        if (isset($response['error'])) {
            throw new WikibaseAPIClientException($response['error']['info']);
        }

        return $response;
    }

    /**
     * Make a direct wbparsevalue API request.
     * You should probably use {@link parseValues} instead.
     *
     * @param string $property property ID
     * @param string[] $values up to 50
     * @return Response
     * @throws WikibaseValueParserException If one of the values cannot be parsed.
     */
    public function parseValuesForProperty(string $property, array $values): Response
    {
        try {
            return $this->get('wbparsevalue', [
                'values' => $this->list($values),
                'property' => $property,
                'validate' => true
            ]);
        } catch (WikibaseAPIClientException $e) {
            throw new WikibaseValueParserException($e->getMessage());
        }
    }

    /**
     * Parse the given values into Wikibase data values.
     *
     * @param string[][] $valuesByPropertyId Two-dimensional array.
     * The outer level is indexed by property ID;
     * the inner levels are lists (keys ignored) of plain values to be parsed.
     * @return string[][] Two-dimensional array.
     * The outer level is indexed by property ID;
     * the inner level maps the unparsed value (as in $valuesByPropertyId)
     * to the parsed value (a string – JSON-serialized).
     * @throws WikibaseValueParserException If one of the values cannot be parsed.
     */
    public function parseValues(array $valuesByPropertyId): array
    {
        $parsed = [];
        foreach ($valuesByPropertyId as $propertyId => $values) {
            $parsed[$propertyId] = collect($values)
                ->unique()
                ->chunk(50) // wbparsevalues allows up to 50 values per request
                ->map(function ($chunk) use ($propertyId) {
                    $response = $this->parseValuesForProperty($propertyId, $chunk->toArray());
                    $results = [];
                    foreach ($response['results'] as $result) {
                        $results[$result['raw']] = json_encode($result);
                    }
                    return $results;
                })
                ->collapse()
                ->toArray();
        }
        return $parsed;
    }

    /**
     * Make a direct wbformatvalue API request.
     * You should probably use {@link formatValues} instead.
     *
     * @param string $property property ID
     * @param string $value Wikibase data value (JSON-serialized)
     * @param string $lang language code
     * @return Response
     */
    public function formatValueForProperty(string $property, string $value, string $lang): Response
    {
        return $this->get('wbformatvalue', [
            'generate' => 'text/plain',
            'datavalue' => $value,
            'property' => $property,
            'uselang' => $lang,
        ]);
    }

    /**
     * Format the given data values into plain text.
     *
     * @param string[][] $valuesByPropertyId Two-dimensional array.
     * The outer level is indexed by property ID;
     * the inner level contains the parsed data values (JSON-serialized).
     * The return value of {@link parseValues} can be used here.
     * @param string $lang language code
     * @return string[][] Two-dimensional array.
     * The outer level is indexed by property ID;
     * the inner level maps the original keys to formatted values (plain text strings).
     * If $valuesByPropertyId was the result of {@link parseValues},
     * then the inner keys will be the original unparsed values passed into {@link parseValues}.
     */
    public function formatValues(array $valuesByPropertyId, string $lang): array
    {
        $formatted = [];
        foreach ($valuesByPropertyId as $propertyId => $values) {
            $formatted[$propertyId] = collect($values)
                ->unique()
                // no chunk, wbformatvalue only supports 1 value per request :(
                ->map(function (string $value) use ($lang, $propertyId) {
                    $response = $this->formatValueForProperty($propertyId, $value, $lang);
                    return $response['result'];
                })
                ->toArray();
        }
        return $formatted;
    }

    public function formatEntities(array $ids, string $lang): Response
    {
        $response = $this->get('wbformatentities', [
            'ids' => $this->list($ids),
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

    public function getEntities(array $ids, array $props): Response
    {
        return $this->get('wbgetentities', [
            'ids' => $this->list($ids),
            'props' => $this->list($props),
        ]);
    }

    public function getPropertyDatatypes(array $ids): array
    {
        return collect($ids)
            ->chunk(50) // wbgetentities allows up to 50 IDs per request
            ->map(function ($chunk) {
                $response = $this->getEntities($chunk->toArray(), ['datatype']);
                return array_column($response['entities'], 'datatype', 'id');
            })
            ->collapse()
            ->toArray();
    }
}
