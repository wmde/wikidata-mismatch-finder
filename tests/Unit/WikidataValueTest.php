<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Mockery;
use App\Services\WikibaseAPIClient;
use App\Rules\WikidataValue;
use App\Exceptions\WikibaseValueParserException;

class WikidataValueTest extends TestCase
{
    public function test_passes_validation_when_client_returns(): void
    {
        $mockApiClient = Mockery::mock(WikibaseAPIClient::class);
        $mockApiClient->shouldReceive('parseValue')->once();

        $rule = new WikidataValue($mockApiClient);

        $this->assertTrue($rule->passes('some-attribute', [
            'property' => 'P1234',
            'value' => 'some-value'
        ]));
    }

    public function test_fails_validation_when_client_throws(): void
    {
        $mockApiClient = Mockery::mock(WikibaseAPIClient::class);
        $mockApiClient->shouldReceive('parseValue')->andThrow(
            new WikibaseValueParserException()
        );

        $rule = new WikidataValue($mockApiClient);

        $this->assertFalse($rule->passes('some-attribute', [
            'property' => 'P1234',
            'value' => 'some-value'
        ]));
    }
}
