<?php

declare(strict_types=1);

namespace Nilambar\Classifier\Tests;

use Nilambar\Classifier\Classifier;
use PHPUnit\Framework\TestCase;

/**
 * Classifier Test.
 *
 * @since 1.0.0
 */
class ClassifierTest extends TestCase
{
    /**
     * Test data for classification.
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $test_data;

    /**
     * Set up test data.
     *
     * @since 1.0.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->test_data = [
            [
                'file' => '/privatepanels/class-debug-bar-deprecated.php',
                'line' => 133,
                'column' => 24,
                'type' => 'ERROR',
                'code' => 'PRTCodingStandard.Language.I18nTextDomain.MissingDomainRequired',
                'severity' => 7,
                'message' => 'Missing text domain parameter in function call to __().',
                'docs' => 'https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/',
            ],
            [
                'file' => '/privatepanels/class-debug-bar-deprecated.php',
                'line' => 133,
                'column' => 33,
                'type' => 'ERROR',
                'code' => 'WordPress.WP.I18n.MissingArgDomain',
                'severity' => 7,
                'message' => 'Missing $domain parameter in function call to __().',
                'docs' => 'https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/',
            ],
            [
                'file' => '/privatepanels/class-debug-bar-deprecated.php',
                'line' => 136,
                'column' => 24,
                'type' => 'ERROR',
                'code' => 'PRTCodingStandard.Language.I18nTextDomain.MissingDomainRequired',
                'severity' => 7,
                'message' => 'Missing text domain parameter in function call to __().',
                'docs' => 'https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/',
            ],
            [
                'file' => '/privatepanels/class-debug-bar-deprecated.php',
                'line' => 136,
                'column' => 33,
                'type' => 'ERROR',
                'code' => 'WordPress.WP.I18n.MissingArgDomain',
                'severity' => 7,
                'message' => 'Missing $domain parameter in function call to __().',
                'docs' => 'https://developer.wordpress.org/plugins/internationalization/how-to-internationalize-your-plugin/',
            ],
            [
                'file' => 'debug-bar.php',
                'line' => 0,
                'column' => 0,
                'type' => 'ERROR',
                'code' => 'plugin_header_missing_something',
                'severity' => 9,
                'message' => 'Missing "License" in Plugin Header. Please update your Plugin Header with a valid GPLv2 (or later) compatible license.',
                'docs' => 'https://developer.wordpress.org/plugins/wordpress-org/common-issues/#no-gpl-compatible-license-declared',
            ],
            [
                'file' => 'readme.txt',
                'line' => 0,
                'column' => 0,
                'type' => 'ERROR',
                'code' => 'no_license',
                'severity' => 9,
                'message' => 'Missing "License". Please update your readme with a valid GPLv2 (or later) compatible license.',
                'docs' => 'https://developer.wordpress.org/plugins/wordpress-org/common-issues/#no-gpl-compatible-license-declared',
            ],
            [
                'file' => 'readme.txt',
                'line' => 0,
                'column' => 0,
                'type' => 'WARNING',
                'code' => 'readme_reserved_contributors',
                'severity' => 6,
                'message' => 'The "Contributors" header in the readme file contains reserved username(s). Found: "wordpressdotorg"',
                'docs' => 'https://developer.wordpress.org/plugins/wordpress-org/how-your-readme-txt-works/#readme-header-information',
            ],
        ];
    }

    /**
     * Test classifier instantiation with valid config file.
     *
     * @since 1.0.0
     */
    public function testClassifierInstantiation(): void
    {
        $config_file = __DIR__ . '/data/test-groups.json';
        $classifier = new Classifier($config_file);

        $this->assertInstanceOf(Classifier::class, $classifier);
    }

    /**
     * Test classifier instantiation with invalid config file.
     *
     * @since 1.0.0
     */
    public function testClassifierInstantiationWithInvalidConfig(): void
    {
        $config_file = __DIR__ . '/../data/nonexistent.json';
        $classifier = new Classifier($config_file);

        $this->assertInstanceOf(Classifier::class, $classifier);
    }

    /**
     * Test classification of data with default code field.
     *
     * @since 1.0.0
     */
    public function testClassifyDataWithDefaultCodeField(): void
    {
        $config_file = __DIR__ . '/data/test-groups.json';
        $classifier = new Classifier($config_file);

        $result = $classifier->classify($this->test_data);

        $this->assertIsArray($result);
        $this->assertNotEmpty($result);

        // Check that i18n issues are properly classified.
        $this->assertArrayHasKey('i18n', $result);
        $this->assertNotEmpty($result['i18n']);

        // Check that plugin readme issues are properly classified.
        $this->assertArrayHasKey('plugin_readme', $result);
        $this->assertNotEmpty($result['plugin_readme']);

        // Check that plugin header issues are properly classified.
        $this->assertArrayHasKey('plugin_header', $result);
        $this->assertNotEmpty($result['plugin_header']);
    }



    /**
     * Test classification with empty data.
     *
     * @since 1.0.0
     */
    public function testClassifyEmptyData(): void
    {
        $config_file = __DIR__ . '/data/test-groups.json';
        $classifier = new Classifier($config_file);

        $result = $classifier->classify([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test classification with data that has no matching codes.
     *
     * @since 1.0.0
     */
    public function testClassifyDataWithNoMatchingCodes(): void
    {
        $config_file = __DIR__ . '/data/test-groups.json';
        $classifier = new Classifier($config_file);

        $unmatched_data = [
            [
                'file' => 'test.php',
                'line' => 10,
                'type' => 'ERROR',
                'code' => 'UNKNOWN_CODE_THAT_DOES_NOT_MATCH',
                'message' => 'Test message',
            ],
        ];

        $result = $classifier->classify($unmatched_data);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('ungrouped', $result);
        $this->assertNotEmpty($result['ungrouped']);
        $this->assertCount(1, $result['ungrouped']);
    }

    /**
     * Test that i18n issues are properly classified.
     *
     * @since 1.0.0
     */
    public function testI18nIssuesClassification(): void
    {
        $config_file = __DIR__ . '/data/test-groups.json';
        $classifier = new Classifier($config_file);

        $i18n_data = [
            [
                'file' => 'test.php',
                'line' => 10,
                'type' => 'ERROR',
                'code' => 'WordPress.WP.I18n.MissingArgDomain',
                'message' => 'Missing domain parameter',
            ],
            [
                'file' => 'test.php',
                'line' => 15,
                'type' => 'ERROR',
                'code' => 'PRTCodingStandard.Language.I18nTextDomain.MissingDomainRequired',
                'message' => 'Missing text domain parameter',
            ],
        ];

        $result = $classifier->classify($i18n_data);

        $this->assertArrayHasKey('i18n', $result);
        $this->assertArrayHasKey('ungrouped', $result);
        $this->assertCount(1, $result['i18n']);
        $this->assertCount(1, $result['ungrouped']);
    }

    /**
     * Test that plugin readme issues are properly classified.
     *
     * @since 1.0.0
     */
    public function testPluginReadmeIssuesClassification(): void
    {
        $config_file = __DIR__ . '/data/test-groups.json';
        $classifier = new Classifier($config_file);

        $readme_data = [
            [
                'file' => 'readme.txt',
                'line' => 0,
                'type' => 'ERROR',
                'code' => 'no_license',
                'message' => 'Missing license',
            ],
            [
                'file' => 'readme.txt',
                'line' => 0,
                'type' => 'WARNING',
                'code' => 'readme_reserved_contributors',
                'message' => 'Reserved contributors',
            ],
        ];

        $result = $classifier->classify($readme_data);

        $this->assertArrayHasKey('plugin_readme', $result);
        $this->assertCount(2, $result['plugin_readme']);
    }

    /**
     * Test that plugin header issues are properly classified.
     *
     * @since 1.0.0
     */
    public function testPluginHeaderIssuesClassification(): void
    {
        $config_file = __DIR__ . '/data/test-groups.json';
        $classifier = new Classifier($config_file);

        $header_data = [
            [
                'file' => 'plugin.php',
                'line' => 0,
                'type' => 'ERROR',
                'code' => 'plugin_header_missing_something',
                'message' => 'Missing license in header',
            ],
        ];

        $result = $classifier->classify($header_data);

        $this->assertArrayHasKey('plugin_header', $result);
        $this->assertCount(1, $result['plugin_header']);
    }

    /**
     * Test classification with mixed data types.
     *
     * @since 1.0.0
     */
    public function testClassificationWithMixedDataTypes(): void
    {
        $config_file = __DIR__ . '/data/test-groups.json';
        $classifier = new Classifier($config_file);

        $mixed_data = [
            [
                'file' => 'test.php',
                'line' => 10,
                'type' => 'ERROR',
                'code' => 'WordPress.WP.I18n.MissingArgDomain',
                'message' => 'I18n issue',
            ],
            [
                'file' => 'readme.txt',
                'line' => 0,
                'type' => 'ERROR',
                'code' => 'no_license',
                'message' => 'Readme issue',
            ],
            [
                'file' => 'plugin.php',
                'line' => 0,
                'type' => 'ERROR',
                'code' => 'plugin_header_missing_something',
                'message' => 'Header issue',
            ],
            [
                'file' => 'unknown.php',
                'line' => 0,
                'type' => 'ERROR',
                'code' => 'UNKNOWN_CODE',
                'message' => 'Unknown issue',
            ],
        ];

        $result = $classifier->classify($mixed_data);

        $this->assertArrayHasKey('i18n', $result);
        $this->assertArrayHasKey('plugin_readme', $result);
        $this->assertArrayHasKey('plugin_header', $result);
        $this->assertArrayHasKey('ungrouped', $result);

        $this->assertCount(1, $result['i18n']);
        $this->assertCount(1, $result['plugin_readme']);
        $this->assertCount(1, $result['plugin_header']);
        $this->assertCount(1, $result['ungrouped']);
    }

    /**
     * Test that data maintains original structure after classification.
     *
     * @since 1.0.0
     */
    public function testDataStructurePreservation(): void
    {
        $config_file = __DIR__ . '/data/test-groups.json';
        $classifier = new Classifier($config_file);

        $test_item = [
            'file' => 'test.php',
            'line' => 10,
            'column' => 5,
            'type' => 'ERROR',
            'code' => 'WordPress.WP.I18n.MissingArgDomain',
            'severity' => 7,
            'message' => 'Test message',
            'docs' => 'https://example.com',
        ];

        $result = $classifier->classify([$test_item]);

        $this->assertArrayHasKey('i18n', $result);
        $this->assertCount(1, $result['i18n']);

        $classified_item = $result['i18n'][0];
        $this->assertEquals($test_item, $classified_item);
    }
}
