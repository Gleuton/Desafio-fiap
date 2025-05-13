<?php

namespace FiapAdmin\Exceptions;

use Exception;
use JsonException;
use Throwable;

class ValidationException extends Exception
{
    protected $message;

    /**
     * @throws JsonException
     */
    public function __construct(string $field, string $messageTxt = '', int $code = 422, ?Throwable $previous = null) {
        $message = [
            $field => $messageTxt
        ];
        parent::__construct(json_encode($message, JSON_THROW_ON_ERROR), $code, $previous);
    }
}