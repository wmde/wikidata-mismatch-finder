<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\Storage;
use App\Services\CSVImportReader;
use Closure;
use App\Exceptions\ImportParserException;

class CSVImportReaderTest extends TestCase
{
    // Since PHPUnit data providers are evaluated earlier than Laravel we
    // cannot use Laravel specific helpers such as __() or config().
    // Therefore, we provide a closure, to be called at the appropriate time.
    // See: https://technicallyfletch.com/how-to-use-laravel-factories-inside-a-data-provider/
    public function skippedLinesProvider(): iterable
    {
        yield 'no skipped lines' => [function () {
            return [];
        }];

        yield 'skips empty lines' => [function () {
            return [''];
        }];

        yield 'skips lines with all empty values' => [function (array $cols) {
            return array_fill(0, count($cols), '');
        }];
    }

    /**
     * @dataProvider skippedLinesProvider
     */
    public function test_parses_mismatch_lines($data)
    {
        $filename = 'import.csv';
        $columns = config('imports.upload.column_keys');
        $skip = $data($columns);

        $fakeLines = [
            ["some-statement-guid","some-pid","some-data","some-meta-data","more-data","a-url"],
            ["another-statement-guid","another-pid","another-data","another-meta-data","different-data","no-url"]
        ];

        $fakeCSVContent = join("\n", array_map(function (array $line) {
            return join(',', $line);
        }, array_merge([$columns], [$skip], $fakeLines, [$skip]))); // Add extra lines

        Storage::fake('local');
        Storage::put($filename, $fakeCSVContent);

        $reader = new CSVImportReader();
        $actual = $reader->lines(Storage::path($filename))->values()->all();

        $this->assertEquals(array_map(function ($row) use ($columns) {
            return array_combine($columns, $row);
        }, $fakeLines), $actual);
    }

    public function unparsableLineProvider(): iterable
    {
        yield 'too few columns' => [
            function (array $config): array {
                $colCount = count($config['column_keys']);

                return [
                    join(',', $config['column_keys']) . "\n"
                    . str_repeat(',', $colCount - 2), // Emulate one column too few
                    __('parsing.import.columns', [
                        'amount' => $colCount
                    ])
                ];
            }
        ];

        yield 'too many columns' => [
            function (array $config): array {
                $colCount = count($config['column_keys']);

                return [
                    join(',', $config['column_keys']) . "\n"
                    . str_repeat(',', $colCount), // Emulate one column too many
                    __('parsing.import.columns', [
                        'amount' => $colCount
                    ])
                ];
            }
        ];

        yield 'unrecognized header line' => [
            function (array $config): array {
                return [
                    'some,form,of,non,headers', // Emulate unrecognized headers
                    __('parsing.import.headers', [
                        'header-list' => join(', ', $config['column_keys'])
                    ])
                ];
            }
        ];
    }

    /**
     * @dataProvider unparsableLineProvider
     */
    public function test_throws_parsing_errors(Closure $data)
    {
        $filename = 'unparsable-import.csv';
        $config = config('imports.upload');

        [$content, $message] = $data($config);

        Storage::fake('local');
        Storage::put($filename, $content);

        $this->expectException(ImportParserException::class);
        $this->expectExceptionMessage($message);

        $reader = new CSVImportReader();
        $reader->lines(Storage::path($filename))->all();
    }
}
