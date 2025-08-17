<?php
/**
 * ClassifierException
 *
 * @package Classifier
 */

declare(strict_types=1);

namespace Nilambar\Classifier\Exception;

/**
 * Base exception class for Classifier library.
 *
 * @since 1.0.0
 */
class ClassifierException extends \Exception
{
    /**
     * Error code.
     *
     * @var string
     */
    protected string $error_code;

    /**
     * Error data.
     *
     * @var array
     */
    protected array $error_data;

    /**
     * Constructor.
     *
     * @param string $message    Error message.
     * @param string $error_code Error code.
     * @param array  $data       Error data.
     * @param int    $code       Exception code.
     * @param \Throwable|null $previous Previous exception.
     */
    public function __construct(
        string $message = '',
        string $error_code = '',
        array $data = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $this->error_code = $error_code;
        $this->error_data = $data;

        parent::__construct($message, $code, $previous);
    }

    /**
     * Gets the error code.
     *
     * @return string Error code.
     */
    public function getErrorCode(): string
    {
        return $this->error_code;
    }

    /**
     * Gets the error data.
     *
     * @return array Error data.
     */
    public function getErrorData(): array
    {
        return $this->error_data;
    }
}
