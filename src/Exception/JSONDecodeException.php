<?php

declare(strict_types=1);

namespace Nilambar\Classifier\Exception;

/**
 * Exception thrown when JSON decoding fails.
 *
 * @since 1.0.0
 */
class JSONDecodeException extends ClassifierException
{
    /**
     * Constructor.
     *
     * @param string $json_string The JSON string that failed to decode.
     * @param string $error_message The JSON error message.
     * @param \Throwable|null $previous Previous exception.
     */
    public function __construct(string $json_string, string $error_message, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('JSON decode error: %s', $error_message),
            'json_decode_error',
            [
                'json_string' => $json_string,
                'json_error' => $error_message,
            ],
            0,
            $previous
        );
    }
}
