<?php

/**
 * Example usage of the Classifier.
 *
 * Demonstrates the classifier workflow:
 * 1. Plugin provides JSON file path (via constructor)
 * 2. Validate JSON content with schema
 * 3. If failed, do not proceed
 * 4. If pass, then group based on that JSON file config
 */

require_once 'vendor/autoload.php';

use Nilambar\Classifier\Classifier;

// Sample data to classify
$sample_data = [
    [
        'code' => 'trademark_prefix_test',
        'message' => 'Trademark prefix issue found',
        'type' => 'error'
    ],
    [
        'code' => 'WordPress.Security.NonceVerification',
        'message' => 'Security nonce verification missing',
        'type' => 'warning'
    ],
    [
        'code' => 'plugin_header_version',
        'message' => 'Plugin header version issue',
        'type' => 'error'
    ],
    [
        'code' => 'unknown_issue',
        'message' => 'Some unknown issue',
        'type' => 'info'
    ]
];

echo "=== Classifier Example ===\n\n";

// Example 1: Successful workflow
echo "1. Successful Workflow:\n";
echo "------------------------\n";

$json_file_path = __DIR__ . '/data/groups.json';

try {
    // Initialize classifier with JSON file path
    $classifier = new Classifier($json_file_path);

    // Execute the workflow
    $result = $classifier->classify($sample_data);

    if (empty($result)) {
        echo "❌ Validation failed. Process stopped.\n";
    } else {
        echo "✅ Validation passed. Data classified successfully.\n\n";

        // Display results
        foreach ($result as $group_id => $items) {
            echo "Group: {$group_id}\n";
            echo "Items: " . count($items) . "\n";

            foreach ($items as $item) {
                echo "  - {$item['code']}: {$item['message']} ({$item['type']})\n";
            }
            echo "\n";
        }
    }
} catch (Exception $e) {
    echo "❌ Constructor failed: " . $e->getMessage() . "\n";
}

echo "\n";

// Example 2: Validation failure
echo "2. Validation Failure:\n";
echo "----------------------\n";

$invalid_json_file_path = __DIR__ . '/data/non-existent-groups.json';

try {
    // Initialize classifier with non-existent JSON file path
    $classifier = new Classifier($invalid_json_file_path);

    // Execute the workflow
    $result = $classifier->classify($sample_data);

    if (empty($result)) {
        echo "❌ Validation failed. Process stopped.\n";
        echo "This is the expected behavior when validation fails.\n";
    } else {
        echo "✅ Validation passed. Data classified successfully.\n";
    }
} catch (Exception $e) {
    echo "❌ Constructor failed: " . $e->getMessage() . "\n";
    echo "This is the expected behavior when JSON file is invalid.\n";
}
