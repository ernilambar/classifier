<?php

/**
 * Example usage of the Classifier package.
 *
 * This file demonstrates how to use the Classifier to group and categorize data.
 */

require_once __DIR__ . '/vendor/autoload.php';

use Nilambar\Classifier\Classifier;
use Nilambar\Classifier\Utils\GroupUtils;

// Sample data to classify.
$sample_data = [
    [
        'code'    => 'trademark_wordpress',
        'type'    => 'error',
        'message' => 'WordPress trademark violation',
        'file'    => 'plugin.php',
        'line'    => 10,
    ],
    [
        'code'    => 'WordPress.Security.NonceVerification',
        'type'    => 'warning',
        'message' => 'Nonce verification missing',
        'file'    => 'admin.php',
        'line'    => 25,
    ],
    [
        'code'    => 'readme_missing_header',
        'type'    => 'error',
        'message' => 'Readme header is missing',
        'file'    => 'readme.txt',
        'line'    => 1,
    ],
    [
        'code'    => 'plugin_header_missing',
        'type'    => 'error',
        'message' => 'Plugin header is missing',
        'file'    => 'plugin.php',
        'line'    => 1,
    ],
    [
        'code'    => 'WordPress.WP.I18n.MissingTextDomain',
        'type'    => 'warning',
        'message' => 'Missing text domain',
        'file'    => 'functions.php',
        'line'    => 15,
    ],
    [
        'code'    => 'unknown_error_code',
        'type'    => 'error',
        'message' => 'Unknown error',
        'file'    => 'unknown.php',
        'line'    => 5,
    ],
];

// Initialize the classifier with configuration and schema files.
$config_file = __DIR__ . '/data/groups.json';
$schema_file = __DIR__ . '/data/groups-schema.json';

$classifier = new Classifier($config_file, $schema_file);

echo "=== Basic Classification ===\n";
$classified_data = $classifier->classify($sample_data);

foreach ($classified_data as $group_id => $items) {
    echo "\nGroup: {$group_id}\n";
    echo "Items: " . count($items) . "\n";

    foreach ($items as $item) {
        echo "  - {$item['code']}: {$item['message']}\n";
    }
}

echo "\n=== Grouped by Type ===\n";
$grouped_by_type = GroupUtils::groupByType($sample_data, $classifier->getGroupConfig());

foreach ($grouped_by_type['categories'] as $category) {
    echo "\nCategory: {$category['name']}\n";

    foreach ($category['types'] as $type => $items) {
        echo "  {$type}: " . count($items) . " items\n";

        foreach ($items as $item) {
            echo "    - {$item['code']}: {$item['message']}\n";
        }
    }
}

echo "\n=== JSON Validation Example ===\n";

// Example of JSON validation.
$json_string = '{"test": "value"}';

try {
    $validation_result = Classifier::validateJson($json_string, $schema_file);
    echo "JSON validation successful.\n";
} catch (Exception $e) {
    echo "Validation failed: " . $e->getMessage() . "\n";
}
