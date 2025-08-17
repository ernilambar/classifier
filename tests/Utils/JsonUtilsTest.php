<?php

declare(strict_types=1);

namespace Nilambar\Classifier\Tests\Utils;

use Nilambar\Classifier\Exception\FileNotFoundException;
use Nilambar\Classifier\Exception\JSONDecodeException;
use Nilambar\Classifier\Exception\ValidationException;
use Nilambar\Classifier\Utils\JsonUtils;
use PHPUnit\Framework\TestCase;

/**
 * JsonUtils Test.
 *
 * @since 1.0.0
 */
class JsonUtilsTest extends TestCase
{
    /**
     * Test directory for temporary files.
     *
     * @since 1.0.0
     *
     * @var string
     */
    private $test_dir;

    /**
     * Set up test environment.
     *
     * @since 1.0.0
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->test_dir = sys_get_temp_dir() . '/classifier_test_' . uniqid();
        if (!is_dir($this->test_dir)) {
            mkdir($this->test_dir, 0755, true);
        }
    }

    /**
     * Tear down test environment.
     *
     * @since 1.0.0
     */
    protected function tearDown(): void
    {
        if (is_dir($this->test_dir)) {
            $this->removeDirectory($this->test_dir);
        }

        parent::tearDown();
    }

    /**
     * Remove directory recursively.
     *
     * @since 1.0.0
     *
     * @param string $dir Directory to remove.
     */
    private function removeDirectory(string $dir): void
    {
        if (is_dir($dir)) {
            $files = array_diff(scandir($dir), ['.', '..']);
            foreach ($files as $file) {
                $path = $dir . '/' . $file;
                if (is_dir($path)) {
                    $this->removeDirectory($path);
                } else {
                    unlink($path);
                }
            }
            rmdir($dir);
        }
    }

    /**
     * Test readJson with valid JSON file.
     *
     * @since 1.0.0
     */
    public function testReadJsonWithValidFile(): void
    {
        $json_content = '{"test": "value", "number": 123, "array": [1, 2, 3]}';
        $file_path = $this->test_dir . '/valid.json';
        file_put_contents($file_path, $json_content);

        $result = JsonUtils::readJson($file_path);

        $this->assertIsArray($result);
        $this->assertEquals('value', $result['test']);
        $this->assertEquals(123, $result['number']);
        $this->assertEquals([1, 2, 3], $result['array']);
    }

    /**
     * Test readJson with non-existent file.
     *
     * @since 1.0.0
     */
    public function testReadJsonWithNonExistentFile(): void
    {
        $file_path = $this->test_dir . '/nonexistent.json';

        $this->expectException(FileNotFoundException::class);
        JsonUtils::readJson($file_path);
    }

    /**
     * Test readJson with unreadable file.
     *
     * @since 1.0.0
     */
    public function testReadJsonWithUnreadableFile(): void
    {
        $file_path = $this->test_dir . '/unreadable.json';
        file_put_contents($file_path, '{"test": "value"}');
        chmod($file_path, 0000);

        $this->expectException(FileNotFoundException::class);
        JsonUtils::readJson($file_path);
    }

    /**
     * Test readJson with invalid JSON file.
     *
     * @since 1.0.0
     */
    public function testReadJsonWithInvalidJson(): void
    {
        $json_content = '{"test": "value", "invalid": }';
        $file_path = $this->test_dir . '/invalid.json';
        file_put_contents($file_path, $json_content);

        $this->expectException(JSONDecodeException::class);
        JsonUtils::readJson($file_path);
    }

    /**
     * Test readJson with empty file.
     *
     * @since 1.0.0
     */
    public function testReadJsonWithEmptyFile(): void
    {
        $file_path = $this->test_dir . '/empty.json';
        file_put_contents($file_path, '');

        $this->expectException(JSONDecodeException::class);
        JsonUtils::readJson($file_path);
    }

    /**
     * Test isValidJson with valid JSON string.
     *
     * @since 1.0.0
     */
    public function testIsValidJsonWithValidString(): void
    {
        $valid_json = '{"test": "value", "number": 123}';

        $result = JsonUtils::isValidJson($valid_json);

        $this->assertTrue($result);
    }

    /**
     * Test isValidJson with invalid JSON string.
     *
     * @since 1.0.0
     */
    public function testIsValidJsonWithInvalidString(): void
    {
        $invalid_json = '{"test": "value", "invalid": }';

        $result = JsonUtils::isValidJson($invalid_json);

        $this->assertFalse($result);
    }

    /**
     * Test isValidJson with empty string.
     *
     * @since 1.0.0
     */
    public function testIsValidJsonWithEmptyString(): void
    {
        $result = JsonUtils::isValidJson('');

        $this->assertFalse($result);
    }

    /**
     * Test isValidJson with null string.
     *
     * @since 1.0.0
     */
    public function testIsValidJsonWithNullString(): void
    {
        $result = JsonUtils::isValidJson('null');

        $this->assertTrue($result);
    }

    /**
     * Test validateJsonStringWithSchema with valid JSON and schema.
     *
     * @since 1.0.0
     */
    public function testValidateJsonStringWithSchemaWithValidData(): void
    {
        $json_string = '{"name": "test", "age": 25}';
        $schema_content = '{
            "type": "object",
            "properties": {
                "name": {"type": "string"},
                "age": {"type": "integer"}
            },
            "required": ["name", "age"]
        }';
        $schema_file = $this->test_dir . '/schema.json';
        file_put_contents($schema_file, $schema_content);

        $result = JsonUtils::validateJsonStringWithSchema($json_string, $schema_file);

        $this->assertTrue($result);
    }

    /**
     * Test validateJsonStringWithSchema with invalid JSON string.
     *
     * @since 1.0.0
     */
    public function testValidateJsonStringWithSchemaWithInvalidJson(): void
    {
        $invalid_json = '{"name": "test", "invalid": }';
        $schema_content = '{"type": "object"}';
        $schema_file = $this->test_dir . '/schema.json';
        file_put_contents($schema_file, $schema_content);

        $this->expectException(JSONDecodeException::class);
        JsonUtils::validateJsonStringWithSchema($invalid_json, $schema_file);
    }

    /**
     * Test validateJsonStringWithSchema with non-existent schema file.
     *
     * @since 1.0.0
     */
    public function testValidateJsonStringWithSchemaWithNonExistentSchema(): void
    {
        $json_string = '{"name": "test"}';
        $schema_file = $this->test_dir . '/nonexistent_schema.json';

        $this->expectException(FileNotFoundException::class);
        JsonUtils::validateJsonStringWithSchema($json_string, $schema_file);
    }

    /**
     * Test validateJsonStringWithSchema with invalid schema.
     *
     * @since 1.0.0
     */
    public function testValidateJsonStringWithSchemaWithInvalidSchema(): void
    {
        $json_string = '{"name": "test"}';
        $invalid_schema = '{"type": "object", "invalid": }';
        $schema_file = $this->test_dir . '/invalid_schema.json';
        file_put_contents($schema_file, $invalid_schema);

        $this->expectException(JSONDecodeException::class);
        JsonUtils::validateJsonStringWithSchema($json_string, $schema_file);
    }

    /**
     * Test validateJsonDataWithSchema with valid data and schema.
     *
     * @since 1.0.0
     */
    public function testValidateJsonDataWithSchemaWithValidData(): void
    {
        $data = ['name' => 'test', 'age' => 25];
        $schema_content = '{
            "type": "object",
            "properties": {
                "name": {"type": "string"},
                "age": {"type": "integer"}
            },
            "required": ["name", "age"]
        }';
        $schema_file = $this->test_dir . '/schema.json';
        file_put_contents($schema_file, $schema_content);

        $result = JsonUtils::validateJsonDataWithSchema($data, $schema_file);

        $this->assertTrue($result);
    }

    /**
     * Test validateJsonDataWithSchema with invalid data.
     *
     * @since 1.0.0
     */
    public function testValidateJsonDataWithSchemaWithInvalidData(): void
    {
        $data = ['name' => 'test', 'age' => 'not_a_number'];
        $schema_content = '{
            "type": "object",
            "properties": {
                "name": {"type": "string"},
                "age": {"type": "integer"}
            },
            "required": ["name", "age"]
        }';
        $schema_file = $this->test_dir . '/schema.json';
        file_put_contents($schema_file, $schema_content);

        $this->expectException(ValidationException::class);
        JsonUtils::validateJsonDataWithSchema($data, $schema_file);
    }

    /**
     * Test validateJsonDataWithSchema with missing required fields.
     *
     * @since 1.0.0
     */
    public function testValidateJsonDataWithSchemaWithMissingRequiredFields(): void
    {
        $data = ['name' => 'test'];
        $schema_content = '{
            "type": "object",
            "properties": {
                "name": {"type": "string"},
                "age": {"type": "integer"}
            },
            "required": ["name", "age"]
        }';
        $schema_file = $this->test_dir . '/schema.json';
        file_put_contents($schema_file, $schema_content);

        $this->expectException(ValidationException::class);
        JsonUtils::validateJsonDataWithSchema($data, $schema_file);
    }

    /**
     * Test validateJsonDataWithSchema with non-existent schema file.
     *
     * @since 1.0.0
     */
    public function testValidateJsonDataWithSchemaWithNonExistentSchema(): void
    {
        $data = ['name' => 'test'];
        $schema_file = $this->test_dir . '/nonexistent_schema.json';

        $this->expectException(FileNotFoundException::class);
        JsonUtils::validateJsonDataWithSchema($data, $schema_file);
    }

    /**
     * Test validateJsonDataWithSchema with complex nested data.
     *
     * @since 1.0.0
     */
    public function testValidateJsonDataWithSchemaWithComplexData(): void
    {
        $data = [
            'user' => [
                'name' => 'John Doe',
                'email' => 'john@example.com',
                'addresses' => [
                    [
                        'type' => 'home',
                        'street' => '123 Main St',
                        'city' => 'Anytown',
                    ],
                    [
                        'type' => 'work',
                        'street' => '456 Business Ave',
                        'city' => 'Worktown',
                    ],
                ],
            ],
        ];

        $schema_content = '{
            "type": "object",
            "properties": {
                "user": {
                    "type": "object",
                    "properties": {
                        "name": {"type": "string"},
                        "email": {"type": "string", "format": "email"},
                        "addresses": {
                            "type": "array",
                            "items": {
                                "type": "object",
                                "properties": {
                                    "type": {"type": "string"},
                                    "street": {"type": "string"},
                                    "city": {"type": "string"}
                                },
                                "required": ["type", "street", "city"]
                            }
                        }
                    },
                    "required": ["name", "email", "addresses"]
                }
            },
            "required": ["user"]
        }';
        $schema_file = $this->test_dir . '/complex_schema.json';
        file_put_contents($schema_file, $schema_content);

        $result = JsonUtils::validateJsonDataWithSchema($data, $schema_file);

        $this->assertTrue($result);
    }

    /**
     * Test validateJsonDataWithSchema with object data.
     *
     * @since 1.0.0
     */
    public function testValidateJsonDataWithSchemaWithObjectData(): void
    {
        $data = (object) ['name' => 'test', 'age' => 25];
        $schema_content = '{
            "type": "object",
            "properties": {
                "name": {"type": "string"},
                "age": {"type": "integer"}
            },
            "required": ["name", "age"]
        }';
        $schema_file = $this->test_dir . '/schema.json';
        file_put_contents($schema_file, $schema_content);

        $result = JsonUtils::validateJsonDataWithSchema($data, $schema_file);

        $this->assertTrue($result);
    }

    /**
     * Test validateJsonDataWithSchema with array data.
     *
     * @since 1.0.0
     */
    public function testValidateJsonDataWithSchemaWithArrayData(): void
    {
        $data = ['item1', 'item2', 'item3'];
        $schema_content = '{
            "type": "array",
            "items": {"type": "string"}
        }';
        $schema_file = $this->test_dir . '/array_schema.json';
        file_put_contents($schema_file, $schema_content);

        $result = JsonUtils::validateJsonDataWithSchema($data, $schema_file);

        $this->assertTrue($result);
    }

    /**
     * Test validateJsonDataWithSchema with primitive data.
     *
     * @since 1.0.0
     */
    public function testValidateJsonDataWithSchemaWithPrimitiveData(): void
    {
        $data = 'test string';
        $schema_content = '{"type": "string"}';
        $schema_file = $this->test_dir . '/string_schema.json';
        file_put_contents($schema_file, $schema_content);

        $result = JsonUtils::validateJsonDataWithSchema($data, $schema_file);

        $this->assertTrue($result);
    }
}
