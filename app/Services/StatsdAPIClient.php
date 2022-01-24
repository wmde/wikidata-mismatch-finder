<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Exceptions\StatsdClientException;

/**
 * Client that connects to statsv endpoint to collect usage metrics
 */
class StatsdAPIClient
{

    /**
     * @var string statsd endpoint url
     */
    private $baseUrl;

    public function __construct(string $baseUrl, string $namespace)
    {
        $this->baseUrl = $baseUrl;

        if (!app()->environment('production')) {
            $namespace .= '-test';
        }

        $this->namespace = $namespace;
    }

    public function sendStats(string $metric): Response
    {
        $response = Http::post($this->baseUrl. '?' .$this->namespace . '.' . $metric . '=1c');

        // Checking for an errors field in the response, since Wikibase api
        // responds with 200 even for erroneous requests
        if (isset($response['error'])) {
            throw new StatsdClientException($response['error']['info']);
        }

        return $response;
    }
}
