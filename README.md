# Classifier

A PHP library for classifying and grouping data based on JSON configuration rules with schema validation support.

## Features

- **Flexible Classification**: Group data based on prefix or contains matching rules
- **JSON Schema Validation**: Validate configuration files against JSON schemas
- **WordPress Compatible**: Follows WordPress coding standards and patterns
- **Type-based Grouping**: Group data by type (error, warning, etc.) within categories
- **Standalone Library**: Works both in WordPress and standalone PHP environments

## Installation

```bash
composer require ernilambar/classifier
```

## Usage

### Basic Classification

```php
use Nilambar\Classifier\Classifier;

// Initialize the classifier with configuration and schema files
$classifier = new Classifier(
    '/path/to/groups.json',
    '/path/to/groups-schema.json'
);

// Sample data to classify
$data = [
    [
        'code'    => 'trademark_wordpress',
        'type'    => 'error',
        'message' => 'WordPress trademark violation',
    ],
    [
        'code'    => 'WordPress.Security.NonceVerification',
        'type'    => 'warning',
        'message' => 'Nonce verification missing',
    ],
];

// Classify the data
$classified_data = $classifier->classify($data);

// Process the results
foreach ($classified_data as $group_id => $items) {
    echo "Group: {$group_id}\n";
    foreach ($items as $item) {
        echo "  - {$item['code']}: {$item['message']}\n";
    }
}
```

### Group by Type

```php
use Nilambar\Classifier\Utils\Group_Utils;

// Group data by type within categories
$grouped_by_type = Group_Utils::group_by_type(
    $data,
    $classifier->get_group_config(),
    'code',    // Field containing the classification code
    'type'     // Field containing the type information
);

foreach ($grouped_by_type['categories'] as $category) {
    echo "Category: {$category['name']}\n";

    foreach ($category['types'] as $type => $items) {
        echo "  {$type}: " . count($items) . " items\n";
    }
}
```

### JSON Validation

```php
use Nilambar\Classifier\Classifier;

// Validate JSON string against schema
$json_string = '{"test": "value"}';
$validation_result = Classifier::validate_json($json_string, '/path/to/schema.json');

if (is_wp_error($validation_result)) {
    echo "Validation failed: " . $validation_result->get_error_message();
} else {
    echo "JSON validation successful.";
}
```

## Configuration Format

The classifier uses a JSON configuration file to define grouping rules. Here's an example:

```json
{
  "$schema": "./groups-schema.json",
  "trademark": {
    "id": "trademark",
    "title": "Trademarks",
    "children": {
      "trademark_prefix": {
        "id": "trademark_prefix",
        "title": "Trademarks Prefix",
        "type": "prefix",
        "parent": "trademark",
        "checks": ["trademark_"]
      },
      "trademark_contains": {
        "id": "trademark_contains",
        "title": "Trademarks Contains",
        "type": "contains",
        "parent": "trademark",
        "checks": ["trademark"]
      }
    }
  }
}
```

### Configuration Structure

- **Parent Groups**: Can have children and serve as category containers
- **Child Groups**: Define specific matching rules
- **Type**: Either `prefix` (starts with) or `contains` (contains string)
- **Checks**: Array of strings to match against item codes

## Requirements

- PHP 7.4 or higher
- Composer
- `justinrainbow/json-schema` for schema validation

## License

MIT License
