<?php

namespace Codenson\Daraja\Exceptions;

use Exception;

class DarajaException extends Exception
{
    public function __construct(string $message, int $code = 0, ?Exception $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }

    public function report(): void
    {
        if (config('daraja.logging.enabled')) {
            logger()->error('Daraja API Error: ' . $this->getMessage(), [
                'code' => $this->getCode(),
                'file' => $this->getFile(),
                'line' => $this->getLine(),
            ]);
        }
    }
}