<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use App\Exceptions\StatsdClientException;

class StatsdClient
{

    /**
     * @var string statsd endpoint url
     */
    private $baseUrl;

    public function __construct(string $baseUrl)
    {
        $this->baseUrl = $baseUrl;
    }

    public function sendStats(string $metric): Response
    {
        $response = Http::post($this->baseUrl. '?Wikidata.mismatch-finder.' . $metric . '=1c');

        // Checking for an errors field in the response, since Wikibase api
        // responds with 200 even for erroneous requests
        if (isset($response['error'])) {
            throw new StatsdClientException($response['error']['info']);
        }

        return $response;
    }
}
