<?php

declare(strict_types=1);

namespace Nilambar\Classifier\Exception;

/**
 * Exception thrown when validation fails.
 *
 * @since 1.0.0
 */
class ValidationException extends ClassifierException
{
    /**
     * Constructor.
     *
     * @param array $errors Array of validation errors.
     * @param string $context Context where validation failed.
     * @param \Throwable|null $previous Previous exception.
     */
    public function __construct(array $errors, string $context = '', ?\Throwable $previous = null)
    {
        $error_messages = [];
        foreach ($errors as $error) {
            $error_messages[] = sprintf(
                'Property "%s": %s',
                $error['property'] ?? 'unknown',
                $error['message'] ?? 'Unknown error'
            );
        }

        parent::__construct(
            sprintf('Validation failed: %s', implode('; ', $error_messages)),
            'validation_failed',
            [
                'errors' => $errors,
                'context' => $context,
            ],
            0,
            $previous
        );
    }
}
