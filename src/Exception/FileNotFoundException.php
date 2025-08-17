<?php

declare(strict_types=1);

namespace Nilambar\Classifier\Exception;

/**
 * Exception thrown when a file is not found.
 *
 * @since 1.0.0
 */
class FileNotFoundException extends ClassifierException
{
    /**
     * Constructor.
     *
     * @param string $file_path Path to the file that was not found.
     * @param \Throwable|null $previous Previous exception.
     */
    public function __construct(string $file_path, ?\Throwable $previous = null)
    {
        parent::__construct(
            sprintf('File not found: %s', $file_path),
            'file_not_found',
            ['file_path' => $file_path],
            0,
            $previous
        );
    }
}
