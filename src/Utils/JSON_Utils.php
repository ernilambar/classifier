<?php

declare(strict_types=1);

namespace Nilambar\Classifier\Utils;

use Exception;
use JsonSchema\Validator;
use Nilambar\Classifier\Exception\FileNotFoundException;
use Nilambar\Classifier\Exception\JSONDecodeException;
use Nilambar\Classifier\Exception\ValidationException;

/**
 * JSON utility functions.
 *
 * @since 1.0.0
 */
class JSON_Utils
{
    /**
     * Reads and decodes a JSON file.
     *
     * @param string $file_path Path to the JSON file to read.
     * @return array The decoded JSON data as array.
     * @throws FileNotFoundException When file is not found or not readable.
     * @throws JSONDecodeException When JSON decoding fails.
     */
    public static function read_json(string $file_path): array
    {
        // Check if file exists.
        if (!file_exists($file_path)) {
            throw new FileNotFoundException($file_path);
        }

        // Verify file is readable.
        if (!is_readable($file_path)) {
            throw new FileNotFoundException($file_path);
        }

        $file_content = file_get_contents($file_path);

        // Validate file read operation.
        if (false === $file_content) {
            throw new FileNotFoundException($file_path);
        }

        $json_data = json_decode($file_content, true);

        // Check for JSON parsing errors.
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new JSONDecodeException($file_content, json_last_error_msg());
        }

        return $json_data;
    }

    /**
     * Checks if given string is valid JSON.
     *
     * @param string $str String to check for validity.
     * @return bool True if valid, otherwise false.
     */
    public static function is_valid_json(string $str): bool
    {
        json_decode($str);

        return (JSON_ERROR_NONE === json_last_error());
    }

    /**
     * Validates a JSON string against a schema file.
     *
     * @param string $json_string The JSON string to validate.
     * @param string $schema_file Path to the JSON schema file.
     * @return bool True if valid.
     * @throws JSONDecodeException When JSON decoding fails.
     * @throws ValidationException When validation fails.
     */
    public static function validate_json_string_with_schema(string $json_string, string $schema_file): bool
    {
        // Decode the JSON string to validate.
        $data = json_decode($json_string);
        if (!self::is_valid_json($json_string)) {
            throw new JSONDecodeException($json_string, json_last_error_msg());
        }

        return self::validate_json_data_with_schema($data, $schema_file);
    }

    /**
     * Validates decoded JSON data against a JSON schema file.
     *
     * @param mixed $data The data to validate (usually array or object).
     * @param string $schema_file Path to the JSON schema file.
     * @return bool True if valid.
     * @throws FileNotFoundException When schema file is not found.
     * @throws ValidationException When validation fails.
     */
    public static function validate_json_data_with_schema($data, string $schema_file): bool
    {
        // Read the schema file using existing method.
        $schema_data = self::read_json($schema_file);

        // Convert data to object for validation.
        $json_data = json_decode(json_encode($data));

        try {
            // Use the library for validation.
            $validator = new Validator();

            // Convert schema to object too.
            $schema_object = json_decode(json_encode($schema_data));

            $validator->validate($json_data, $schema_object);

            if ($validator->isValid()) {
                return true;
            }

            // Collect validation errors.
            $errors = [];

            foreach ($validator->getErrors() as $error) {
                $errors[] = [
                    'property' => $error['property'],
                    'message' => $error['message'],
                ];
            }

            throw new ValidationException($errors, 'JSON schema validation');
        } catch (ValidationException $e) {
            // Re-throw validation exceptions.
            throw $e;
        } catch (Exception $e) {
            // Convert other exceptions to validation exceptions.
            throw new ValidationException(
                [['property' => 'unknown', 'message' => $e->getMessage()]],
                'JSON schema validation',
                $e
            );
        }
    }
}
