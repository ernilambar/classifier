# Classifier

A PHP library for classifying and grouping data based on JSON configuration rules with schema validation support.

## Features

- **Flexible Classification**: Group data based on prefix or contains matching rules
- **JSON Schema Validation**: Validate configuration files against JSON schemas

## Installation

```bash
composer require ernilambar/classifier
```

## Usage

### Workflow

The classifier follows this simple workflow:

1. Plugin provides JSON file path (via constructor)
2. Validate JSON content with schema
3. If failed, do not proceed
4. If pass, then group based on that JSON file config

```php
use Nilambar\Classifier\Classifier;

// Define JSON file path.
$json_file_path = '/path/to/groups.json';

// Initialize classifier with JSON file path.
$classifier = new Classifier($json_file_path);

// Sample data to classify.
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

// Execute the workflow
$result = $classifier->classify($data);

if (empty($result)) {
    echo "Validation failed. Process stopped.";
} else {
    // Process the classified data
    foreach ($result as $group_id => $items) {
        echo "Group: {$group_id}\n";
        foreach ($items as $item) {
            echo "  - {$item['code']}: {$item['message']}\n";
        }
    }
}
```

## Configuration Format

The classifier uses a JSON configuration file to define grouping rules:

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
      }
    }
  }
}
```

## Requirements

- PHP 7.4 or higher
- Composer
- `justinrainbow/json-schema` for schema validation

## License

MIT License
