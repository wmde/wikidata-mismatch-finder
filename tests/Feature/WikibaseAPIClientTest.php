<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use App\Services\WikibaseAPIClient;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Str;
use App\Exceptions\WikibaseAPIClientException;
use App\Exceptions\WikibaseValueParserException;
use Kevinrob\GuzzleCache\CacheMiddleware;
use Mockery;

class WikibaseAPIClientTest extends TestCase
{
    const FAKE_API_URL = "http://fake.wikibase.api";

    public function test_parse_values_returns_values_from_api_responses(): void
    {
        Http::fake(function (Request $req) {
            if ($req->data()['property'] === 'P1') {
                return Http::response(['results' => [
                   ['raw' => 'abc', 'value' => 'abc', 'type' => 'string', 'valid' => true],
                   ['raw' => 'def', 'value' => 'def', 'type' => 'string', 'valid' => true],
                ]], 200);
            } elseif ($req->data()['property'] === 'P2') {
                return Http::response(['results' => [
                   [
                       'raw' => 'Q1',
                       'value' => ['entity-type' => 'item', 'id' => 'Q1'],
                       'type' => 'wikibase-entityid',
                       'valid' => true,
                   ],
                   [
                       'raw' => 'Q2',
                       'value' => ['entity-type' => 'item', 'id' => 'Q2'],
                       'type' => 'wikibase-entityid',
                       'valid' => true,
                   ],
                ]], 200);
            } else {
                $this->fail('Unexpected request');
            }
        });

        $mockCache = Mockery::mock(CacheMiddleware::class)->shouldIgnoreMissing();

        $client = new WikibaseAPIClient(self::FAKE_API_URL, $mockCache);
        $parsed = $client->parseValues([
            'P1' => ['abc', 'def'],
            'P2' => ['Q1', 'Q2'],
        ]);

        $this->assertActionRequest(self::FAKE_API_URL, 'wbparsevalue', [
            'values' => 'abc|def',
            'property' => 'P1',
            'validate' => true,
        ]);
        $this->assertActionRequest(self::FAKE_API_URL, 'wbparsevalue', [
            'values' => 'Q1|Q2',
            'property' => 'P2',
            'validate' => true,
        ]);

        $expected = [
            'P1' => [
                'abc' => '{"raw":"abc","value":"abc","type":"string","valid":true}',
                'def' => '{"raw":"def","value":"def","type":"string","valid":true}',
            ],
            'P2' => [
                'Q1' => '{"raw":"Q1","value":{"entity-type":"item","id":"Q1"},"type":"wikibase-entityid","valid":true}',
                'Q2' => '{"raw":"Q2","value":{"entity-type":"item","id":"Q2"},"type":"wikibase-entityid","valid":true}',
            ],
        ];
        $this->assertSame($expected, $parsed);
    }

    public function test_parse_values_throws_on_error_response()
    {
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
        $client->parseValues(['P1234' => ['fake-value']]);

        $this->assertActionRequest(self::FAKE_API_URL, 'wbparsevalue', [
            'property' => 'P1234',
            'values' => 'fake-value',
        ]);
    }

    public function test_parse_values_empty(): void
    {
        Http::fake(function () {
            $this->fail('should not make an HTTP request');
        });

        $mockCache = Mockery::mock(CacheMiddleware::class)->shouldIgnoreMissing();
        $client = new WikibaseAPIClient(self::FAKE_API_URL, $mockCache);
        $data = $client->parseValues(['P123' => []]);

        $this->assertSame(['P123' => []], $data);
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

    public function test_format_entity_throws_on_error_response()
    {
        $fakeIds = ['P1234', 'Q4321'];
        $fakePayload = [
            'ids' => implode('|', $fakeIds),
            'uselang' => 'en'
        ];
        $fakeErrorResponse = ['error' => [
            'info' => 'not okay'
        ]];

        Http::fake(function (Request $req) use ($fakeErrorResponse) {
            return Http::response($fakeErrorResponse, 200);
        });
        $mockCache = Mockery::mock(CacheMiddleware::class)->shouldIgnoreMissing();

        $this->expectException(WikibaseAPIClientException::class);
        $this->expectExceptionMessage($fakeErrorResponse['error']['info']);

        $client = new WikibaseAPIClient(self::FAKE_API_URL, $mockCache);
        $client->formatEntities($fakeIds, $fakePayload['uselang']);

        $this->assertActionRequest(self::FAKE_API_URL, 'wbformatentities', $fakePayload);
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

    public function test_get_property_datatypes_returns_datatype_array(): void
    {
        $fakeIds = ['P1234', 'P5678'];
        $fakePayload = [
            'action' => 'wbgetentities',
            'format' => 'json',
            'maxage' => null,
            'ids' => implode('|', $fakeIds),
            'props' => 'datatype',
        ];
        $fakeResponseBody = ['entities' => [
            'P1234' => [
                'type' => 'property',
                'datatype' => 'wikibase-item',
                'id' => 'P1234',
            ],
            'P5678' => [
                'type' => 'property',
                'datatype' => 'time',
                'id' => 'P5678',
            ],
        ]];
        $expectedResult = [
            'P1234' => 'wikibase-item',
            'P5678' => 'time',
        ];

        Http::fake(function (Request $req) use ($fakePayload, $fakeResponseBody) {
            $this->assertSame($fakePayload, $req->data());
            return Http::response($fakeResponseBody, 200);
        });

        $mockCache = Mockery::mock(CacheMiddleware::class)->shouldIgnoreMissing();
        $client = new WikibaseAPIClient(self::FAKE_API_URL, $mockCache);
        $data = $client->getPropertyDatatypes($fakeIds);

        $this->assertSame($expectedResult, $data);
    }

    public function test_get_property_datatypes_empty(): void
    {
        Http::fake(function () {
            $this->fail('should not make an HTTP request');
        });

        $mockCache = Mockery::mock(CacheMiddleware::class)->shouldIgnoreMissing();
        $client = new WikibaseAPIClient(self::FAKE_API_URL, $mockCache);
        $data = $client->getPropertyDatatypes([]);

        $this->assertSame([], $data);
    }

    public function methodProvider(): iterable
    {
        yield 'parseValuesForProperty' => ['parseValuesForProperty', ['P1234', ['fake-value']]];
        yield 'formatEntities' => ['formatEntities', [['Q1234'], 'en']];
        yield 'getEntities' => ['getEntities', [['P1234'], ['datatype']]];
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
