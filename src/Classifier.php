<?php

/**
 * Classifier
 *
 * @package Classifier
 */

declare(strict_types=1);

namespace Nilambar\Classifier;

use Nilambar\Classifier\Utils\Group_Utils;
use Nilambar\Classifier\Utils\JSON_Utils;
use Nilambar\Classifier\WordPress\WP_Error;

use function Nilambar\Classifier\WordPress\is_wp_error;

/**
 * Classifier class.
 *
 * Main class for classifying and grouping data based on configuration rules.
 *
 * @since 1.0.0
 */
class Classifier
{
    /**
     * Group configuration data.
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $group_config;

    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @param string $config_file Path to the group configuration JSON file.
     * @param string $schema_file Path to the JSON schema file for validation.
     */
    public function __construct(string $config_file, string $schema_file = '')
    {
        $this->group_config = $this->load_group_config($config_file, $schema_file);
    }

    /**
     * Loads and validates group configuration.
     *
     * @since 1.0.0
     *
     * @param string $config_file Path to the group configuration JSON file.
     * @param string $schema_file Path to the JSON schema file for validation.
     * @return array Array of group definitions.
     */
    private function load_group_config(string $config_file, string $schema_file = ''): array
    {
        // Read and validate the group configuration file.
        $groups = JSON_Utils::read_json($config_file);
        if (is_wp_error($groups)) {
            return [];
        }

        // Validate against schema if provided.
        if (! empty($schema_file)) {
            $validation_result = JSON_Utils::validate_json_data_with_schema($groups, $schema_file);
            if (is_wp_error($validation_result)) {
                return [];
            }
        }

        return Group_Utils::process_group_config($groups);
    }

    /**
     * Classifies data based on the loaded configuration.
     *
     * @since 1.0.0
     *
     * @param array $data Array of data items to classify.
     * @param string $code_field Field name containing the classification code.
     * @return array Classified data organized by groups.
     */
    public function classify(array $data, string $code_field = 'code'): array
    {
        if (empty($this->group_config)) {
            return [];
        }

        return Group_Utils::classify_data($data, $this->group_config, $code_field);
    }

    /**
     * Gets the group configuration.
     *
     * @since 1.0.0
     *
     * @return array Group configuration.
     */
    public function get_group_config(): array
    {
        return $this->group_config;
    }

    /**
     * Validates a JSON string against a schema.
     *
     * @since 1.0.0
     *
     * @param string $json_string JSON string to validate.
     * @param string $schema_file Path to the JSON schema file.
     * @return bool|WP_Error True if valid, WP_Error on failure.
     */
    public static function validate_json(string $json_string, string $schema_file)
    {
        return JSON_Utils::validate_json_string_with_schema($json_string, $schema_file);
    }

    /**
     * Validates JSON data against a schema.
     *
     * @since 1.0.0
     *
     * @param mixed  $data        Data to validate.
     * @param string $schema_file Path to the JSON schema file.
     * @return bool|WP_Error True if valid, WP_Error on failure.
     */
    public static function validate_data($data, string $schema_file)
    {
        return JSON_Utils::validate_json_data_with_schema($data, $schema_file);
    }
}
