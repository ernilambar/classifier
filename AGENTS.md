# AGENTS.md

## Overview

A PHP library for classifying and grouping data based on JSON configuration rules with schema validation support. Core stack: PHP 7.4+, Composer, PHPUnit, PHP_CodeSniffer.

## Setup

```bash
composer install
```

Requires PHP >= 7.4. The autoloader maps `Nilambar\Classifier\` to `src/`.

## Commands

| Task   | Command                                              |
|--------|------------------------------------------------------|
| Lint   | `composer lint`                                      |
| Format | `composer format`                                    |
| Test   | `composer test`                                      |
| Lint PHP | `composer lint-php`                                |
| PHPCS    | `composer phpcs`                                     |

## Conventions

- **Namespace imports**: Use full `use` statements — no leading backslash in `use` declarations, no grouped uses, alphabetically sorted imports. Unused imports are prohibited.
- **No global fallbacks**: All references to functions, classes, and constants must be imported via `use` (global ones are allowed).
- **Line length**: Soft limit 120 characters, absolute hard limit 150.
- **Error handling**: Use typed exceptions from `src/Exception/` (`ClassifierException`, `ValidationException`, etc.). Do not throw generic `Exception` or `Throwable`.
- **JSON config flow**: Always read → validate against schema → process via `GroupUtils`. If validation fails, stop — do not proceed to classification.

## Quality Gate

Before declaring a task complete, run every command below and verify exit code 0:

```bash
composer lint && composer test
```

Or individually:

```bash
composer lint-php && composer phpcs && composer test
```
