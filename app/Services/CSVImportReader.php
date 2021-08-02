<?php

namespace App\Services;

use Illuminate\Support\LazyCollection;
use App\Exceptions\ImportParserException;

class CSVImportReader
{
    public function lines(string $path): LazyCollection
    {
        $lines = LazyCollection::make(function () use ($path) {
            $file = fopen($path, 'r');

            while ($data = fgetcsv($file)) {
                yield $data;
            }
        });

        $allowedKeys = config('imports.upload.column_keys');
        $columnKeys = $lines->get(0);

        if (count(array_diff($allowedKeys, $columnKeys)) !== 0) {
            throw new ImportParserException(0, __('parsing.import.headers', [
                'header-list' => join(", ", $allowedKeys)
            ]));
        }

        return $lines->except(0)
            ->filter(function (array $row) {
                return $row[0] !== null
                && $row !== array_fill(0, 5, '');
            })
            ->map(function (array $row, int $i) use ($columnKeys) {
                if (count($row) !== count($columnKeys)) {
                    throw new ImportParserException($i, __('parsing.import.columns', [
                        'amount' => count($columnKeys)
                    ]));
                }

                return array_combine($columnKeys, $row);
            });
    }
}
