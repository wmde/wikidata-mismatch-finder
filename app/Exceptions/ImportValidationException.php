<?php

namespace App\Exceptions;

use Exception;
use App\Models\ImportMeta;
use Throwable;

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
        ImportMeta $import,
        int $line,
        string $message = '',
        int $code = 0,
        Throwable $previous = null
    ) {
        parent::__construct(__('validation.import.error', [
            'line' => $line,
            'message' => $message
        ]), $code, $previous);

        $this->import = $import;
        $this->csvLine = $line;
    }

    /**
     * Get the exception's context information.
     */
    public function context(): array
    {
        return [
            'csv_line' => $this->csvLine,
            'import_id' => $this->import->id,
            'user_id' => $this->import->user->id
        ];
    }
}
