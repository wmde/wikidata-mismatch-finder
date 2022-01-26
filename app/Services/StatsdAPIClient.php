<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Exceptions\StatsdClientException;
use Illuminate\Support\Facades\Log;

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

    public function sendStats(string $metric, int $value = 1): Response
    {
        Log::info("sending " . $metric . " metric to statsv: " . $value);
        $response = Http::post($this->baseUrl. '?' .$this->namespace . '.' . $metric . '=' . $value . 'c');
        Log::info("response: " . json_encode($response));

        // Checking for an errors field in the response, since Wikibase api
        // responds with 200 even for erroneous requests
        if (isset($response['error'])) {
            throw new StatsdClientException($response['error']['info']);
        }

        return $response;
    }
}
