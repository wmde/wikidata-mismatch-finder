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

    public function test_format_entities_returns_api_responses(): void
    {
        $fakeIds = ['P1234', 'Q4321'];
        $fakePayload = [
            'ids' => implode('|', $fakeIds),
            'uselang' => 'en'
        ];
        $fakeResponseBody = ['test' => 'okay'];

        Http::fake(function (Request $req) use ($fakeResponseBody) {
            return Http::response($fakeResponseBody, 200);
        });

        $mockCache = Mockery::mock(CacheMiddleware::class)->shouldIgnoreMissing();

        $client = new WikibaseAPIClient(self::FAKE_API_URL, $mockCache);
        $response = $client->formatEntities($fakeIds, $fakePayload['uselang']);

        $this->assertActionRequest(self::FAKE_API_URL, 'wbformatentities', $fakePayload);
        $this->assertSame($fakeResponseBody, $response->json());
    }

    public function test_get_labels_returns_label_array(): void
    {
        $fakeIds = ['P1234', 'Q4321'];
        $fakePayload = [
            'ids' => implode('|', $fakeIds),
            'uselang' => 'en'
        ];

        $fakeResponseBody = ['wbformatentities' => [
            'Q4321' => '<a title="Q4321" href="https://www.wikidata.org/wiki/Q4321">some item</a>',
            'P1234' => '<a title="Property:P1234" href="https://www.wikidata.org/wiki/Property:P1234">some property</a>'
        ]];

        $expectedResult = [
            'Q4321' => 'some item',
            'P1234' => 'some property'
        ];

        Http::fake(function (Request $req) use ($fakeResponseBody) {
            return Http::response($fakeResponseBody, 200);
        });

        $mockCache = Mockery::mock(CacheMiddleware::class)->shouldIgnoreMissing();

        $client = new WikibaseAPIClient(self::FAKE_API_URL, $mockCache);
        $data = $client->getLabels($fakeIds, $fakePayload['uselang']);

        $this->assertEquals($expectedResult, $data);
    }

    public function methodProvider(): iterable
    {
        yield 'parseValue' => ['parseValue', ['P1234', 'fake-value']];
        yield 'formatEntities' => ['formatEntities', [['Q1234'], 'en']];
    }

    /**
     * @dataProvider methodProvider
     */
    public function test_all_methods_retrieve_cached_responses($methodName, $args): void
    {
        $fakeResponseBody = ['test' => 'okay'];

        $mockCache = Mockery::mock(CacheMiddleware::class);
        // The `__invoke()` magic method is utilized by guzzle middleware:
        // https://www.phptutorial.net/php-oop/php-__invoke/
        $mockCache->shouldReceive('__invoke')->andReturn(function () use ($fakeResponseBody) {
            return Http::response($fakeResponseBody, 200);
        });

        $client = new WikibaseAPIClient(self::FAKE_API_URL, $mockCache);
        $response = $client->$methodName(...$args);

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
