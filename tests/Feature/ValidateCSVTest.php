<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\ImportMeta;
use Illuminate\Support\Facades\Storage;
use App\Jobs\ValidateCSV;
use App\Exceptions\ImportValidationException;
use Closure;
use Faker\Generator;
use Mockery\MockInterface;
use App\Rules\WikidataValue;
use Exception;
use Throwable;
use Illuminate\Support\Facades\Validator;
use App\Services\CSVImportReader;
use App\Exceptions\ImportParserException;

class ValidateCSVTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    // Since PHPUnit data providers are evaluated earlier than Laravel we
    // cannot use Laravel specific helpers such as __() or config().
    // Therefore, we provide a closure, to be called at the appropriate time.
    // See: https://technicallyfletch.com/how-to-use-laravel-factories-inside-a-data-provider/
    public function invalidLineProvider(): iterable
    {
        yield 'missing statement guid' => [
            function (array $config): array {
                return [
                    ',P569,3 April 1934,1934-04-03,http://www.example.com', // Emulate missing guid
                    __('validation.required', [
                        'attribute' => 'statement guid'
                    ])
                ];
            }
        ];

        yield 'long statement guid' => [
            function (array $config, Generator $faker): array {
                $longQID= $faker->numerify('Q' . str_repeat('#', $config['guid']['max_length']));

                return [
                    $longQID . '$' . $faker->uuid() . ',' // Emulate long guid
                    . 'P569,3 April 1934,1934-04-03,http://www.example.com', // Ensure correct columns
                    __('validation.max.string', [
                        'attribute' => 'statement guid',
                        'max' => $config['guid']['max_length']
                    ])
                ];
            }
        ];

        yield 'malformed statement guid' => [
            function (array $config): array {
                return [
                    'some-malformed-guid,' // Emulate malformed guid
                    . 'P569,3 April 1934,1934-04-03,http://www.example.com', // Ensure correct columns
                    __('validation.regex', [
                        'attribute' => 'statement guid'
                    ])
                ];
            }
        ];

        yield 'missing property id' => [
            function (array $config): array {
                return [
                    'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5,,' // Emulate missing property id
                    . '3 April 1934,1934-04-03,http://www.example.com', // Ensure correct columns
                    __('validation.required', [
                        'attribute' => 'property id'
                    ])
                ];
            }
        ];

        yield 'long property id' => [
            function (array $config, Generator $faker): array {
                $longPID= $faker->numerify('P' . str_repeat('#', $config['pid']['max_length']));

                return [
                     'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5,' // Ensure correct columns
                    . $longPID . ',' // Emulate long pid
                    . '3 April 1934,1934-04-03,http://www.example.com', // Ensure correct columns
                    __('validation.max.string', [
                        'attribute' => 'property id',
                        'max' => $config['pid']['max_length']
                     ])
                ];
            }
        ];

        yield 'malformed property id' => [
            function (array $config): array {
                return [
                    'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5,' // Ensure correct columns
                    . 'some-malformed-pid,' // Emulate malformed pid
                    . '3 April 1934,1934-04-03,http://www.example.com', // Ensure correct columns
                    __('validation.regex', [
                        'attribute' => 'property id'
                    ])
                ];
            }
        ];

        yield 'missing wikidata value' => [
            function (array $config): array {
                return [
                    'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5,P569' // Ensure correct columns
                    . ',,1934-04-03,http://www.example.com', // Emulate missing wikidata value
                    __('validation.required', [
                        'attribute' => 'wikidata value'
                    ])
                ];
            }
        ];

        yield 'long wikidata value' => [
            function (array $config): array {
                $longValue = str_repeat('a', $config['wikidata_value']['max_length'] + 10);

                return [
                    'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5,P569,' // Ensure correct columns
                    . $longValue . ',1934-04-03,http://www.example.com', // Emulate long wikidata value
                    __('validation.max.string', [
                        'attribute' => 'wikidata value',
                        'max' => $config['wikidata_value']['max_length']
                     ])
                ];
            }
        ];

        yield 'missing external value' => [
            function (array $config): array {
                return [
                    'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5,P569,3 April 1934' // Ensure correct columns
                    . ',,http://www.example.com', // Emulate missing external data
                    __('validation.required', [
                        'attribute' => 'external value'
                    ])
                ];
            }
        ];

        yield 'long external value' => [
            function (array $config, Generator $faker): array {
                $longValue= str_repeat('-', $config['external_value']['max_length'] + 10);

                return [
                    'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5,P569,3 April 1934,' // Ensure correct cols
                    . $longValue . ',http://www.example.com', // Emulate long value
                    __('validation.max.string', [
                        'attribute' => 'external value',
                        'max' => $config['external_value']['max_length']
                    ])
                ];
            }
        ];

        yield 'long external url' => [
            function (array $config, Generator $faker): array {
                $longURL= $faker->url() . '/' . str_repeat('a', $config['external_url']['max_length']);

                return [
                    'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5,P569,3 April 1934,1934-04-03,' // Ensure correct cols
                    . $longURL, // Emulate long url
                    __('validation.max.string', [
                        'attribute' => 'external url',
                        'max' => $config['external_url']['max_length']
                    ])
                ];
            }
        ];

        yield 'malformed external url' => [
            function (array $config): array {
                return [
                    'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5,P569,3 April 1934,1934-04-03,' // Ensure correct cols
                    . 'i-am-not-your-gurl',
                    __('validation.url', [
                        'attribute' => 'external url'
                    ])
                ];
            }
        ];
    }

    /**
     * @dataProvider invalidLineProvider
     */
    public function test_throws_on_invalid_line(Closure $data): void
    {
        $config = array_merge(
            config('imports.upload'),
            config('mismatches.validation')
        );

        [$line, $message] = $data($config, $this->faker);

        // Emulate passed validation rule, as this is not the system under test
        $this->partialMock(WikidataValue::class, function (MockInterface $mock) {
            $mock->shouldReceive('passes')->andReturn(true);
        });

        $import = $this->createMockImport($line);

        $this->expectValidationException($message);

        ValidateCSV::dispatch($import);
    }

    public function test_does_not_throw_on_generic_uuid(): void
    {
        // Ensure correct columns
        $line = 'Q4115189$ffa51229-4877-3135-a2e2-a22fe9b7039d' // Emulate non v4 UUID
                . ',P569,3 April 1934,1934-04-03,http://www.example.com';

        // Emulate passed validation rule, as this is not the system under test
        $this->partialMock(WikidataValue::class, function (MockInterface $mock) {
            $mock->shouldReceive('passes')->andReturn(true);
        });

        $import = $this->createMockImport($line);

        ValidateCSV::dispatch($import);

        $this->assertDatabaseMissing('import_meta', [
            'id' => $import->id,
            'status' => 'failed'
        ]);
    }

    public function test_throws_on_malformed_wikidata_value(): void
    {
        // Ensure correct columns
        $line = 'Q184746$7814880A-A6EF-40EC-885E-F46DD58C8DC5,P569,invalid-wikidata-value,'
                . '1934-04-03,http://www.example.com';
        $message = __('validation.wikidata_value', [
            'attribute' => 'wikidata value'
        ]);

        // Emulate failed validation rule
        $this->partialMock(WikidataValue::class, function (MockInterface $mock) {
            $mock->shouldReceive('passes')->andReturn(false);
        });

        $import = $this->createMockImport($line);

        $this->expectValidationException($message);
        ValidateCSV::dispatch($import);
    }

    public function failureProvider()
    {
        yield 'validator failure' => [
            function () {
                Validator::shouldReceive('make')->once()->andThrow(Exception::class);
            }
        ];

        yield 'reader failure' => [
            function ($instance) {
                $instance->mock(CSVImportReader::class, function ($mock) {
                    $mock->shouldReceive('lines')->once()->andThrow(new ImportParserException(0));
                });
            }
        ];
    }

    /**
     * @dataProvider failureProvider
     */
    public function test_fails_on_thrown_exceptions(Closure $failSetup): void
    {
        $failSetup($this);

        // Ensure rows
        $import = $this->createMockImport('some-guid,some-pid,some-value,another-value,a-url');

        try {
            ValidateCSV::dispatch($import);
        } catch (Throwable $ignored) {
            $this->assertDatabaseHas('import_meta', [
                'id' => $import->id,
                'status' => 'failed'
            ]);
        }
    }

    private function createMockImport(string $content): ImportMeta
    {
        $headers = join(',', config('imports.upload.column_keys'));

        Storage::fake('local');
        Storage::put(
            'mismatch-files/invalid-import.csv',
            $headers . "\n" . $content // Add header line
        );

        $user = User::factory()->uploader()->create();
        return ImportMeta::factory()->for($user)->create([
            'filename' => 'invalid-import.csv',
            'status' => 'pending'
        ]);
    }

    private function expectValidationException(string $message): void
    {
        $this->expectException(ImportValidationException::class);
        $this->expectExceptionMessage($message);
    }
}
