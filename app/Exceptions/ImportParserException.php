<?php

namespace App\Exceptions;

use Exception;
use Throwable;

/**
 * Exception to be thrown upon errors when parsing uploaded mismatch files
 */
class ImportParserException extends Exception
{
    /**
     * @var int
     */
    private $csvLine;

    public function __construct(
        int $line,
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(__('parsing.import.error', [
            'line' => $line,
            'message' => $message
        ]), $code, $previous);

        $this->csvLine = $line;
        $this->parserMessage = $message;
    }

    /**
     * Get the exception's context information.
     *
     * @return array
     */
    public function context(): array
    {
        return [
            'csv_line' => $this->csvLine,
            'parser_message' => $this->parserMessage
        ];
    }
}
