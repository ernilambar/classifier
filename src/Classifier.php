<?php

/**
 * Classifier
 *
 * @package Classifier
 */

declare(strict_types=1);

namespace Nilambar\Classifier;

use Nilambar\Classifier\Utils\GroupUtils;
use Nilambar\Classifier\Utils\JsonUtils;

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
        $this->group_config = $this->loadGroupConfig($config_file, $schema_file);
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
    private function loadGroupConfig(string $config_file, string $schema_file = ''): array
    {
        try {
            // Read and validate the group configuration file.
            $groups = JsonUtils::readJson($config_file);

            // Validate against schema if provided.
            if (! empty($schema_file)) {
                JsonUtils::validateJsonDataWithSchema($groups, $schema_file);
            }

            return GroupUtils::processGroupConfig($groups);
        } catch (Exception $e) {
            // Return empty array on any error.
            return [];
        }
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

        return GroupUtils::classifyData($data, $this->group_config, $code_field);
    }

    /**
     * Gets the group configuration.
     *
     * @since 1.0.0
     *
     * @return array Group configuration.
     */
    public function getGroupConfig(): array
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
     * @return bool True if valid.
     * @throws Exception When validation fails.
     */
    public static function validateJson(string $json_string, string $schema_file): bool
    {
        return JsonUtils::validateJsonStringWithSchema($json_string, $schema_file);
    }

    /**
     * Validates JSON data against a schema.
     *
     * @since 1.0.0
     *
     * @param mixed  $data        Data to validate.
     * @param string $schema_file Path to the JSON schema file.
     * @return bool True if valid.
     * @throws Exception When validation fails.
     */
    public static function validateData($data, string $schema_file): bool
    {
        return JsonUtils::validateJsonDataWithSchema($data, $schema_file);
    }
}
