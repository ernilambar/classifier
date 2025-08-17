<?php

declare(strict_types=1);

namespace Nilambar\Classifier\Tests\Utils;

use Nilambar\Classifier\Utils\GroupUtils;
use PHPUnit\Framework\TestCase;

/**
 * GroupUtils Test.
 *
 * @since 1.0.0
 */
class GroupUtilsTest extends TestCase
{
    /**
     * Test data for group configuration.
     *
     * @since 1.0.0
     *
     * @var array
     */
    private $test_group_config;

    /**
     * Set up test data.
     *
     * @since 1.0.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->test_group_config = [
            '$schema' => './groups-schema.json',
            'i18n' => [
                'id' => 'i18n',
                'title' => 'Internationalization',
                'children' => [
                    'i18n_prefix' => [
                        'id' => 'i18n_prefix',
                        'title' => 'Internationalization Prefix',
                        'type' => 'prefix',
                        'parent' => 'i18n',
                        'checks' => ['WordPress.WP.I18n'],
                    ],
                    'i18n_contains' => [
                        'id' => 'i18n_contains',
                        'title' => 'Internationalization Contains',
                        'type' => 'contains',
                        'parent' => 'i18n',
                        'checks' => [
                            'Language.I18nFunctionParameters',
                            'PluginCheck.CodeAnalysis.DiscouragedFunctions.load_plugin_textdomain',
                        ],
                    ],
                ],
            ],
            'plugin_readme' => [
                'id' => 'plugin_readme',
                'title' => 'Plugin Readme',
                'children' => [
                    'plugin_readme_contains' => [
                        'id' => 'plugin_readme_contains',
                        'title' => 'Plugin Readme Contains',
                        'type' => 'contains',
                        'parent' => 'plugin_readme',
                        'checks' => ['no_license'],
                    ],
                    'plugin_readme_prefix' => [
                        'id' => 'plugin_readme_prefix',
                        'title' => 'Plugin Readme Prefix',
                        'type' => 'prefix',
                        'parent' => 'plugin_readme',
                        'checks' => ['readme_'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Test processGroupConfig with valid configuration.
     *
     * @since 1.0.0
     */
    public function testProcessGroupConfigWithValidConfig(): void
    {
        $result = GroupUtils::processGroupConfig($this->test_group_config);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('i18n', $result);
        $this->assertArrayHasKey('i18n_prefix', $result);
        $this->assertArrayHasKey('i18n_contains', $result);
        $this->assertArrayHasKey('plugin_readme', $result);
        $this->assertArrayHasKey('plugin_readme_contains', $result);
        $this->assertArrayHasKey('plugin_readme_prefix', $result);

        // Check parent group structure.
        $this->assertEquals('i18n', $result['i18n']['id']);
        $this->assertEquals('Internationalization', $result['i18n']['title']);

        // Check child group structure.
        $this->assertEquals('i18n_prefix', $result['i18n_prefix']['id']);
        $this->assertEquals('prefix', $result['i18n_prefix']['type']);
        $this->assertEquals('i18n', $result['i18n_prefix']['parent']);

        // Check child group structure.
        $this->assertEquals('plugin_readme_contains', $result['plugin_readme_contains']['id']);
        $this->assertEquals('contains', $result['plugin_readme_contains']['type']);
        $this->assertEquals('plugin_readme', $result['plugin_readme_contains']['parent']);

        $this->assertEquals('plugin_readme_prefix', $result['plugin_readme_prefix']['id']);
        $this->assertEquals('prefix', $result['plugin_readme_prefix']['type']);
        $this->assertEquals('plugin_readme', $result['plugin_readme_prefix']['parent']);
    }

    /**
     * Test processGroupConfig with empty configuration.
     *
     * @since 1.0.0
     */
    public function testProcessGroupConfigWithEmptyConfig(): void
    {
        $result = GroupUtils::processGroupConfig([]);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test processGroupConfig with configuration containing only schema.
     *
     * @since 1.0.0
     */
    public function testProcessGroupConfigWithOnlySchema(): void
    {
        $config = ['$schema' => './groups-schema.json'];
        $result = GroupUtils::processGroupConfig($config);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test getItemCategoryId with prefix match.
     *
     * @since 1.0.0
     */
    public function testGetItemCategoryIdWithPrefixMatch(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);
        $code = 'WordPress.WP.I18n.MissingArgDomain';

        $result = GroupUtils::getItemCategoryId($code, $processed_groups);

        $this->assertEquals('i18n', $result);
    }

    /**
     * Test getItemCategoryId with contains match.
     *
     * @since 1.0.0
     */
    public function testGetItemCategoryIdWithContainsMatch(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);
        $code = 'Language.I18nFunctionParameters.Missing';

        $result = GroupUtils::getItemCategoryId($code, $processed_groups);

        $this->assertEquals('i18n', $result);
    }

    /**
     * Test getItemCategoryId with no match.
     *
     * @since 1.0.0
     */
    public function testGetItemCategoryIdWithNoMatch(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);
        $code = 'UNKNOWN_CODE_THAT_DOES_NOT_MATCH';

        $result = GroupUtils::getItemCategoryId($code, $processed_groups);

        $this->assertEquals('ungrouped', $result);
    }

    /**
     * Test getItemCategoryId with empty groups.
     *
     * @since 1.0.0
     */
    public function testGetItemCategoryIdWithEmptyGroups(): void
    {
        $code = 'WordPress.WP.I18n.MissingArgDomain';

        $result = GroupUtils::getItemCategoryId($code, []);

        $this->assertEquals('ungrouped', $result);
    }

    /**
     * Test classifyData with valid data.
     *
     * @since 1.0.0
     */
    public function testClassifyDataWithValidData(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);
        $data = [
            [
                'file' => 'test.php',
                'line' => 10,
                'type' => 'ERROR',
                'code' => 'WordPress.WP.I18n.MissingArgDomain',
                'message' => 'Missing domain parameter',
            ],
            [
                'file' => 'readme.txt',
                'line' => 0,
                'type' => 'ERROR',
                'code' => 'no_license',
                'message' => 'Missing license',
            ],
        ];

        $result = GroupUtils::classifyData($data, $processed_groups);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('i18n', $result);
        $this->assertArrayHasKey('plugin_readme', $result);
        $this->assertCount(1, $result['i18n']);
        $this->assertCount(1, $result['plugin_readme']);
    }

    /**
     * Test classifyData with empty data.
     *
     * @since 1.0.0
     */
    public function testClassifyDataWithEmptyData(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);

        $result = GroupUtils::classifyData([], $processed_groups);

        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    /**
     * Test classifyData with custom code field.
     *
     * @since 1.0.0
     */
    public function testClassifyDataWithCustomCodeField(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);
        $data = [
            [
                'file' => 'test.php',
                'line' => 10,
                'type' => 'ERROR',
                'custom_code' => 'WordPress.WP.I18n.MissingArgDomain',
                'message' => 'Missing domain parameter',
            ],
        ];

        $result = GroupUtils::classifyData($data, $processed_groups, 'custom_code');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('i18n', $result);
        $this->assertCount(1, $result['i18n']);
    }

    /**
     * Test classifyData with unmatched codes.
     *
     * @since 1.0.0
     */
    public function testClassifyDataWithUnmatchedCodes(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);
        $data = [
            [
                'file' => 'test.php',
                'line' => 10,
                'type' => 'ERROR',
                'code' => 'UNKNOWN_CODE_THAT_DOES_NOT_MATCH',
                'message' => 'Unknown issue',
            ],
        ];

        $result = GroupUtils::classifyData($data, $processed_groups);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('ungrouped', $result);
        $this->assertCount(1, $result['ungrouped']);
    }

    /**
     * Test classifyData maintains order.
     *
     * @since 1.0.0
     */
    public function testClassifyDataMaintainsOrder(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);
        $data = [
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
        ];

        $result = GroupUtils::classifyData($data, $processed_groups);

        $this->assertIsArray($result);
        $keys = array_keys($result);
        $this->assertEquals('i18n', $keys[0]);
        $this->assertEquals('plugin_readme', $keys[1]);
    }

    /**
     * Test groupByType with valid data.
     *
     * @since 1.0.0
     */
    public function testGroupByTypeWithValidData(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);
        $data = [
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
                'type' => 'WARNING',
                'code' => 'Language.I18nFunctionParameters',
                'message' => 'Function parameters issue',
            ],
            [
                'file' => 'readme.txt',
                'line' => 0,
                'type' => 'ERROR',
                'code' => 'no_license',
                'message' => 'Missing license',
            ],
        ];

        $result = GroupUtils::groupByType($data, $processed_groups);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertCount(2, $result['categories']);

        // Check first category (i18n).
        $i18n_category = $result['categories'][0];
        $this->assertEquals('Internationalization', $i18n_category['name']);
        $this->assertArrayHasKey('errors', $i18n_category['types']);
        $this->assertArrayHasKey('warnings', $i18n_category['types']);
        $this->assertCount(1, $i18n_category['types']['errors']);
        $this->assertCount(1, $i18n_category['types']['warnings']);

        // Check second category (plugin_readme).
        $readme_category = $result['categories'][1];
        $this->assertEquals('Plugin Readme', $readme_category['name']);
        $this->assertArrayHasKey('errors', $readme_category['types']);
        $this->assertCount(1, $readme_category['types']['errors']);
    }

    /**
     * Test groupByType with empty data.
     *
     * @since 1.0.0
     */
    public function testGroupByTypeWithEmptyData(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);

        $result = GroupUtils::groupByType([], $processed_groups);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertEmpty($result['categories']);
    }

    /**
     * Test groupByType with custom type field.
     *
     * @since 1.0.0
     */
    public function testGroupByTypeWithCustomTypeField(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);
        $data = [
            [
                'file' => 'test.php',
                'line' => 10,
                'custom_type' => 'ERROR',
                'code' => 'WordPress.WP.I18n.MissingArgDomain',
                'message' => 'Missing domain parameter',
            ],
        ];

        $result = GroupUtils::groupByType($data, $processed_groups, 'code', 'custom_type');

        $this->assertIsArray($result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertCount(1, $result['categories']);
    }

    /**
     * Test groupByType with case insensitive type matching.
     *
     * @since 1.0.0
     */
    public function testGroupByTypeWithCaseInsensitiveType(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);
        $data = [
            [
                'file' => 'test.php',
                'line' => 10,
                'type' => 'error',
                'code' => 'WordPress.WP.I18n.MissingArgDomain',
                'message' => 'Missing domain parameter',
            ],
            [
                'file' => 'test.php',
                'line' => 15,
                'type' => 'WARNING',
                'code' => 'Language.I18nFunctionParameters',
                'message' => 'Function parameters issue',
            ],
        ];

        $result = GroupUtils::groupByType($data, $processed_groups);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertCount(1, $result['categories']);

        $category = $result['categories'][0];
        $this->assertArrayHasKey('errors', $category['types']);
        $this->assertArrayHasKey('warnings', $category['types']);
        $this->assertCount(1, $category['types']['errors']);
        $this->assertCount(1, $category['types']['warnings']);
    }

    /**
     * Test groupByType with unknown type.
     *
     * @since 1.0.0
     */
    public function testGroupByTypeWithUnknownType(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);
        $data = [
            [
                'file' => 'test.php',
                'line' => 10,
                'type' => 'UNKNOWN_TYPE',
                'code' => 'WordPress.WP.I18n.MissingArgDomain',
                'message' => 'Missing domain parameter',
            ],
        ];

        $result = GroupUtils::groupByType($data, $processed_groups);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertCount(1, $result['categories']);

        $category = $result['categories'][0];
        $this->assertArrayHasKey('other', $category['types']);
        $this->assertCount(1, $category['types']['other']);
    }

    /**
     * Test groupByType with ungrouped items.
     *
     * @since 1.0.0
     */
    public function testGroupByTypeWithUngroupedItems(): void
    {
        $processed_groups = GroupUtils::processGroupConfig($this->test_group_config);
        $data = [
            [
                'file' => 'test.php',
                'line' => 10,
                'type' => 'ERROR',
                'code' => 'UNKNOWN_CODE_THAT_DOES_NOT_MATCH',
                'message' => 'Unknown issue',
            ],
        ];

        $result = GroupUtils::groupByType($data, $processed_groups);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('categories', $result);
        $this->assertCount(1, $result['categories']);

        $category = $result['categories'][0];
        $this->assertEquals('Misc Issues', $category['name']);
        $this->assertArrayHasKey('errors', $category['types']);
        $this->assertCount(1, $category['types']['errors']);
    }
}
