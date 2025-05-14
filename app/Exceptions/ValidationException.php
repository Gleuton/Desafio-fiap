<?php

namespace FiapAdmin\Exceptions;

use Exception;
use JsonException;
use Throwable;

class ValidationException extends Exception
{
    public function __construct(private readonly string $field, string $message = '', int $code = 422, ?Throwable $previous = null) {
        parent::__construct($message, $code, $previous);
    }
    public function getField(): string {
        return $this->field;
    }
}