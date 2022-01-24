<?php

namespace App\Exceptions;

use Exception;
use App\Models\ImportMeta;
use Throwable;

/**
 * Exception to be thrown upon errors when validating uploaded mismatch files
 */
class ImportValidationException extends Exception
{

    /**
     * @var ImportMeta
     */
    private $import;

    /**
     * @var int
     */
    private $csvLine;

    public function __construct(
        int $line = 0,
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(__('validation.import.error', [
            'line' => $line,
            'message' => $message
        ]), $code, $previous);

        $this->csvLine = $line;
    }

    /**
     * Get the exception's context information.
     */
    public function context(): array
    {
        return [
            'csv_line' => $this->csvLine
        ];
    }
}
