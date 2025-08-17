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

}
