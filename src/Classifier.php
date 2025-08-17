<?php

/**
 * Classifier
 *
 * @package Classifier
 */

declare(strict_types=1);

namespace Nilambar\Classifier;

use Exception;
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
     * Validation error message.
     *
     * @since 1.0.0
     *
     * @var string|null
     */
    private $validation_error;

    /**
     * Whether the configuration loaded successfully.
     *
     * @since 1.0.0
     *
     * @var bool
     */
    private $is_valid;

    /**
     * Constructor.
     *
     * @since 1.0.0
     *
     * @param string $config_file Path to the group configuration JSON file.
     */
    public function __construct(string $config_file)
    {
        $schema_file = __DIR__ . '/../data/groups-schema.json';
        $this->loadGroupConfig($config_file, $schema_file);
    }

    /**
     * Loads and validates group configuration.
     *
     * @since 1.0.0
     *
     * @param string $config_file Path to the group configuration JSON file.
     * @param string $schema_file Path to the JSON schema file for validation.
     */
    private function loadGroupConfig(string $config_file, string $schema_file = ''): void
    {
        try {
            // Read and validate the group configuration file.
            $groups = JsonUtils::readJson($config_file);

            // Validate against schema if provided.
            if (! empty($schema_file)) {
                JsonUtils::validateJsonDataWithSchema($groups, $schema_file);
            }

            $this->group_config = GroupUtils::processGroupConfig($groups);
            $this->is_valid = true;
            $this->validation_error = null;
        } catch (Exception $e) {
            // Store the error for later retrieval.
            $this->group_config = [];
            $this->is_valid = false;
            $this->validation_error = $e->getMessage();
        }
    }

    /**
     * Checks if the configuration loaded successfully.
     *
     * @since 1.0.0
     *
     * @return bool True if configuration is valid, false otherwise.
     */
    public function isValid(): bool
    {
        return $this->is_valid;
    }

    /**
     * Gets the validation error message if configuration failed to load.
     *
     * @since 1.0.0
     *
     * @return string|null Validation error message or null if no error.
     */
    public function getValidationError(): ?string
    {
        return $this->validation_error;
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
        if (! $this->is_valid || empty($this->group_config)) {
            return [];
        }

        return GroupUtils::classifyData($data, $this->group_config, $code_field);
    }
}
