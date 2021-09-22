<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\WikibaseAPIClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Str;
use App\Exceptions\WikibaseValueParserException;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Mockery;

class WikibaseAPIClientTest extends TestCase
{
    const FAKE_API_URL = "http://fake.wikibase.api";

    /**
     * A basic unit test example.
     *
     * @return void
     */
    public function test_parse_value_returns_api_response(): void
    {
        $fakePayload = [
            'property' => 'P1234',
            'values' => 'fake-value'
        ];
        $fakeResponseBody = ['test' => 'okay'];

        Http::fake(function (Request $req) use ($fakeResponseBody) {
            return Http::response($fakeResponseBody, 200);
        });

        $mockCache = Mockery::mock(CacheMiddleware::class)->shouldIgnoreMissing();

        $client = new WikibaseAPIClient(self::FAKE_API_URL, $mockCache);
        $response = $client->parseValue($fakePayload['property'], $fakePayload['values']);

        $this->assertActionRequest(self::FAKE_API_URL, 'wbparsevalue', $fakePayload);
        $this->assertSame($fakeResponseBody, $response->json());
    }

    public function test_parse_value_throws_on_error_response()
    {
        $fakePayload = [
            'property' => 'P1234',
            'values' => 'fake-value'
        ];
        $fakeErrorResponse = ['error' => [
            'info' => 'not okay'
        ]];

        Http::fake(function (Request $req) use ($fakeErrorResponse) {
            return Http::response($fakeErrorResponse, 200);
        });
        $mockCache = Mockery::mock(CacheMiddleware::class)->shouldIgnoreMissing();

        $this->expectException(WikibaseValueParserException::class);
        $this->expectExceptionMessage($fakeErrorResponse['error']['info']);

        $client = new WikibaseAPIClient(self::FAKE_API_URL, $mockCache);
        $client->parseValue($fakePayload['property'], $fakePayload['values']);

        $this->assertActionRequest(self::FAKE_API_URL, 'wbparsevalue', $fakePayload);
    }

    public function test_parse_value_retrieves_cached_responses()
    {
        $fakeResponseBody = ['test' => 'okay'];

        $mockCache = Mockery::mock(CacheMiddleware::class);
        // The `__invoke()` magic method is utilized by guzzle middleware:
        // https://www.phptutorial.net/php-oop/php-__invoke/
        $mockCache->shouldReceive('__invoke')->andReturn(function () use ($fakeResponseBody) {
            return Http::response($fakeResponseBody, 200);
        });

        $client = new WikibaseAPIClient(self::FAKE_API_URL, $mockCache);
        $response = $client->parseValue('P1234', 'fake-value');

        $this->assertSame($fakeResponseBody, $response->json());
    }

    private function assertActionRequest(string $url, string $action, array $payload)
    {
        Http::assertSent(function (Request $req) use ($url, $action, $payload) {
            foreach ($payload as $key => $value) {
                if ($req[$key] != $value) {
                    return false;
                }
            }

            return Str::startsWith($req->url(), $url)
                && $req['action'] == $action;
        });
    }
}
