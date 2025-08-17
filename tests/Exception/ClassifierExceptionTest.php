<?php

declare(strict_types=1);

namespace Nilambar\Classifier\Tests\Exception;

use Nilambar\Classifier\Exception\ClassifierException;
use Nilambar\Classifier\Exception\FileNotFoundException;
use Nilambar\Classifier\Exception\JSONDecodeException;
use Nilambar\Classifier\Exception\ValidationException;
use PHPUnit\Framework\TestCase;

/**
 * Exception Test.
 *
 * @since 1.0.0
 */
class ClassifierExceptionTest extends TestCase
{
    /**
     * Test ClassifierException instantiation.
     *
     * @since 1.0.0
     */
    public function testClassifierException(): void
    {
        $message = 'Test exception message';
        $exception = new ClassifierException($message);

        $this->assertInstanceOf(ClassifierException::class, $exception);
        $this->assertInstanceOf(\Exception::class, $exception);
        $this->assertEquals($message, $exception->getMessage());
    }

    /**
     * Test FileNotFoundException instantiation.
     *
     * @since 1.0.0
     */
    public function testFileNotFoundException(): void
    {
        $file_path = '/path/to/nonexistent/file.json';
        $exception = new FileNotFoundException($file_path);

        $this->assertInstanceOf(FileNotFoundException::class, $exception);
        $this->assertInstanceOf(ClassifierException::class, $exception);
        $this->assertStringContainsString($file_path, $exception->getMessage());
    }

    /**
     * Test JSONDecodeException instantiation.
     *
     * @since 1.0.0
     */
    public function testJSONDecodeException(): void
    {
        $json_string = '{"invalid": json}';
        $error_message = 'Syntax error';
        $exception = new JSONDecodeException($json_string, $error_message);

        $this->assertInstanceOf(JSONDecodeException::class, $exception);
        $this->assertInstanceOf(ClassifierException::class, $exception);
        $this->assertStringContainsString($error_message, $exception->getMessage());
    }

    /**
     * Test ValidationException instantiation without previous exception.
     *
     * @since 1.0.0
     */
    public function testValidationExceptionWithoutPrevious(): void
    {
        $errors = [
            ['property' => 'name', 'message' => 'Name is required'],
            ['property' => 'age', 'message' => 'Age must be a number'],
        ];
        $context = 'JSON schema validation';
        $exception = new ValidationException($errors, $context);

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertInstanceOf(ClassifierException::class, $exception);
        $error_data = $exception->getErrorData();
        $this->assertEquals($errors, $error_data['errors']);
        $this->assertEquals($context, $error_data['context']);
        $this->assertNull($exception->getPrevious());
    }

    /**
     * Test ValidationException instantiation with previous exception.
     *
     * @since 1.0.0
     */
    public function testValidationExceptionWithPrevious(): void
    {
        $errors = [
            ['property' => 'name', 'message' => 'Name is required'],
        ];
        $context = 'JSON schema validation';
        $previous_exception = new \Exception('Previous error');
        $exception = new ValidationException($errors, $context, $previous_exception);

        $this->assertInstanceOf(ValidationException::class, $exception);
        $this->assertInstanceOf(ClassifierException::class, $exception);
        $error_data = $exception->getErrorData();
        $this->assertEquals($errors, $error_data['errors']);
        $this->assertEquals($context, $error_data['context']);
        $this->assertEquals($previous_exception, $exception->getPrevious());
    }

    /**
     * Test ValidationException getErrors method.
     *
     * @since 1.0.0
     */
    public function testValidationExceptionGetErrors(): void
    {
        $errors = [
            ['property' => 'field1', 'message' => 'Error 1'],
            ['property' => 'field2', 'message' => 'Error 2'],
        ];
        $exception = new ValidationException($errors, 'Test context');

        $error_data = $exception->getErrorData();
        $this->assertEquals($errors, $error_data['errors']);
        $this->assertCount(2, $error_data['errors']);
    }

    /**
     * Test ValidationException getContext method.
     *
     * @since 1.0.0
     */
    public function testValidationExceptionGetContext(): void
    {
        $context = 'Custom validation context';
        $exception = new ValidationException([], $context);

        $error_data = $exception->getErrorData();
        $this->assertEquals($context, $error_data['context']);
    }

    /**
     * Test ValidationException with empty errors array.
     *
     * @since 1.0.0
     */
    public function testValidationExceptionWithEmptyErrors(): void
    {
        $exception = new ValidationException([], 'Test context');

        $this->assertInstanceOf(ValidationException::class, $exception);
        $error_data = $exception->getErrorData();
        $this->assertEquals([], $error_data['errors']);
        $this->assertEquals('Test context', $error_data['context']);
    }

    /**
     * Test exception inheritance hierarchy.
     *
     * @since 1.0.0
     */
    public function testExceptionInheritanceHierarchy(): void
    {
        $file_exception = new FileNotFoundException('/test/path');
        $json_exception = new JSONDecodeException('{}', 'Error');
        $validation_exception = new ValidationException([], 'Context');

        // All should inherit from ClassifierException.
        $this->assertInstanceOf(ClassifierException::class, $file_exception);
        $this->assertInstanceOf(ClassifierException::class, $json_exception);
        $this->assertInstanceOf(ClassifierException::class, $validation_exception);

        // All should inherit from base Exception.
        $this->assertInstanceOf(\Exception::class, $file_exception);
        $this->assertInstanceOf(\Exception::class, $json_exception);
        $this->assertInstanceOf(\Exception::class, $validation_exception);
    }
}
