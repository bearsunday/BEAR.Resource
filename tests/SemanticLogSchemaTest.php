<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Fake\SemanticLogger\Module\TestModule;
use BEAR\Resource\Fake\SemanticLogger\Resource\App\Simple;
use BEAR\Resource\SemanticLog\Profile\Compact\CompleteContext;
use BEAR\Resource\SemanticLog\Profile\Compact\ContextFactory;
use BEAR\Resource\SemanticLog\Profile\Compact\ErrorContext;
use BEAR\Resource\SemanticLog\Profile\Compact\OpenContext;
use BEAR\Resource\SemanticLog\SemanticInvoker;
use JsonSchema\Validator;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use RuntimeException;
use Throwable;

use function assert;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function is_array;
use function json_decode;
use function json_encode;
use function serialize;
use function sprintf;
use function sys_get_temp_dir;
use function tempnam;
use function uniqid;
use function unlink;

use const JSON_PRETTY_PRINT;

class SemanticLogSchemaTest extends TestCase
{
    private Validator $validator;

    protected function setUp(): void
    {
        $this->validator = new Validator();
    }

    public function testCompactContextFactory(): void
    {
        $testModule = new TestModule();
        $injector = new Injector($testModule);
        $resource = $injector->getInstance(ResourceInterface::class);
        $invoker = $injector->getInstance(InvokerInterface::class);

        $factory = new ContextFactory();

        // Test createOpenContext
        $resourceInstance = $resource->newInstance('app://self/simple');
        $request = new Request($invoker, $resourceInstance, 'GET', ['id' => 'factory-test']);
        $openContext = $factory->createOpenContext($request);

        $this->assertInstanceOf(OpenContext::class, $openContext);
        $this->assertSame('GET', $openContext->method);
        $this->assertSame('app://self/simple?id=factory-test', $openContext->uri);

        // Test createCompleteContext
        $resourceObject = $resource->get('app://self/simple', ['id' => 'factory-test']);
        $completeContext = $factory->createCompleteContext($resourceObject, $openContext);

        $this->assertInstanceOf(CompleteContext::class, $completeContext);
        $this->assertSame(200, $completeContext->code);
        $this->assertSame('app://self/simple?id=factory-test', $completeContext->uri);

        // Test createErrorContext
        $exception = new RuntimeException('Factory test error');
        $errorContext = $factory->createErrorContext($exception, 'factory-error-id', $openContext);

        $this->assertInstanceOf(ErrorContext::class, $errorContext);
        $this->assertSame('factory-error-id', $errorContext->exceptionId);
        $this->assertStringContainsString('Factory test error', $errorContext->exceptionAsString);

        // Test createErrorContext without openContext
        $errorContextWithoutOpen = $factory->createErrorContext($exception, 'factory-error-2');
        $this->assertInstanceOf(ErrorContext::class, $errorContextWithoutOpen);
        $this->assertSame('factory-error-2', $errorContextWithoutOpen->exceptionId);

        // Test auto-generated exception ID (missing coverage)
        $autoIdErrorContext = $factory->createErrorContext($exception, '');
        $this->assertInstanceOf(ErrorContext::class, $autoIdErrorContext);
        $this->assertStringStartsWith('e-bear-resource-', $autoIdErrorContext->exceptionId);

        // Test static create method for ErrorContext
        $staticErrorContext = ErrorContext::create($exception, 'static-error');
        $this->assertInstanceOf(ErrorContext::class, $staticErrorContext);
        $this->assertSame('static-error', $staticErrorContext->exceptionId);
    }

    public function testSemanticInvokerIntegration(): void
    {
        // Test SemanticInvoker integration through dependency injection
        $testModule = new TestModule();
        $injector = new Injector($testModule);

        $resource = $injector->getInstance(ResourceInterface::class);
        $invoker = $injector->getInstance(InvokerInterface::class);

        // Verify that SemanticInvoker is bound correctly
        $this->assertInstanceOf(SemanticInvoker::class, $invoker);

        // SemanticInvoker automatically handles logging
        $resourceObject = $resource->get('app://self/simple', ['id' => 'test']);

        // Verify the resource call worked (SemanticInvoker handled logging internally)
        $this->assertSame(200, $resourceObject->code);
        $this->assertStringContainsString('simple', (string) $resourceObject->uri);
    }

    public function testErrorContextSchemaWithXhprof(): void
    {
        $exception = new RuntimeException('Test error for schema validation', 500);
        $exceptionId = 'error_' . uniqid();

        // Create error context
        $errorContext = ErrorContext::create($exception, $exceptionId);

        // Add XHProf file path (simulating SemanticInvoker error handling)
        $xhprofFile = tempnam(sys_get_temp_dir(), 'test_error_xhprof_') . '.xhprof';
        file_put_contents($xhprofFile, serialize([
            'main()' => ['ct' => 1, 'wt' => 50, 'mu' => 512],
            'Exception::__construct' => ['ct' => 1, 'wt' => 25, 'mu' => 256],
        ]));

        // Convert to array and add xhprof_file
        $contextArray = [
            'exceptionId' => $errorContext->exceptionId,
            'exceptionAsString' => $errorContext->exceptionAsString,
            'xhprof_file' => $xhprofFile,
        ];

        // Load schema
        $schemaPath = __DIR__ . '/../docs/schema/error-context.json';
        $schemaContent = file_get_contents($schemaPath);
        assert($schemaContent !== false);
        $schema = json_decode($schemaContent);

        // Convert to JSON and back to ensure proper object/array conversion for validation
        $contextJson = json_encode($contextArray);
        assert($contextJson !== false);
        $contextForValidation = json_decode($contextJson);

        // Validate against schema
        $this->validator->validate($contextForValidation, $schema);

        if ($this->validator->isValid()) {
            $this->assertTrue(true, 'Error context with XHProf file validates against schema');
        } else {
            $errors = [];
            foreach ($this->validator->getErrors() as $error) {
                $errors[] = sprintf('[%s] %s', $error['property'], $error['message']);
            }

            $this->fail('Schema validation failed: ' . implode(', ', $errors));
        }

        // Verify XHProf file exists and contains expected data
        $this->assertFileExists($xhprofFile);
        $this->assertIsString($contextArray['xhprof_file']);
        $this->assertEquals($xhprofFile, $contextArray['xhprof_file']);

        // Output to tmp for manual inspection
        $tmpFile = __DIR__ . '/tmp/error_context_with_xhprof.json';
        file_put_contents($tmpFile, json_encode($contextArray, JSON_PRETTY_PRINT));
        $this->assertFileExists($tmpFile);

        // Clean up
        unlink($xhprofFile);
        unlink($tmpFile);
    }

    public function testCompleteContextSchemaWithoutXhprof(): void
    {
        // Test that schema still validates without optional xhprof_file
        $resource = new Simple();
        $resource->uri = new Uri('app://self/simple', ['id' => 'test']);
        $resource->code = 200;
        $resource->headers = ['Content-Type' => 'application/json'];
        $resource->body = ['result' => 'success'];
        $resource->view = '{"result":"success"}';

        $uri = new Uri('app://self/simple', ['id' => 'test']);
        $invoker = new class implements InvokerInterface {
            public function invoke(AbstractRequest $request): ResourceObject
            {
                throw new RuntimeException('Not implemented');
            }
        };
        $request = new Request($invoker, $resource, 'GET', []);
        $openContext = OpenContext::create($request);
        $completeContext = CompleteContext::create($resource, $openContext);

        // Convert to array WITHOUT xhprof_file
        $contextArray = [
            'uri' => $completeContext->uri,
            'code' => $completeContext->code,
            'headers' => $completeContext->headers,
            'body' => $completeContext->body,
            'view' => $completeContext->view,
        ];

        // Load schema
        $schemaPath = __DIR__ . '/../docs/schema/complete-context.json';
        $schemaContent = file_get_contents($schemaPath);
        assert($schemaContent !== false);
        $schema = json_decode($schemaContent);

        // Convert to JSON and back to ensure proper object/array conversion for validation
        $contextJson = json_encode($contextArray);
        assert($contextJson !== false);
        $contextForValidation = json_decode($contextJson);

        // Validate against schema
        $this->validator->validate($contextForValidation, $schema);

        $this->assertTrue($this->validator->isValid(), 'Complete context without XHProf file should still validate');
    }

    public function testErrorContextSchemaWithoutXhprof(): void
    {
        // Test that schema still validates without optional xhprof_file
        $exception = new RuntimeException('Test error without XHProf', 500);
        $exceptionId = 'error_' . uniqid();

        $errorContext = ErrorContext::create($exception, $exceptionId);

        // Convert to array WITHOUT xhprof_file
        $contextArray = [
            'exceptionId' => $errorContext->exceptionId,
            'exceptionAsString' => $errorContext->exceptionAsString,
        ];

        // Load schema
        $schemaPath = __DIR__ . '/../docs/schema/error-context.json';
        $schemaContent = file_get_contents($schemaPath);
        assert($schemaContent !== false);
        $schema = json_decode($schemaContent);

        // Convert to JSON and back to ensure proper object/array conversion for validation
        $contextJson = json_encode($contextArray);
        assert($contextJson !== false);
        $contextForValidation = json_decode($contextJson);

        // Validate against schema
        $this->validator->validate($contextForValidation, $schema);

        $this->assertTrue($this->validator->isValid(), 'Error context without XHProf file should still validate');
    }

    public function testVerboseProfileSchemaValidation(): void
    {
        $testModule = new TestModule();
        $injector = new Injector($testModule);
        $resource = $injector->getInstance(ResourceInterface::class);

        // Make actual resource call to generate Profile data
        $resourceObject = $resource->get('app://self/simple', ['id' => 'profile-schema-test']);

        $this->assertSame(200, $resourceObject->code);

        // Get the actual semantic log with Profile structure
        $semanticLogger = $injector->getInstance(SemanticLoggerInterface::class);
        $logJson = $semanticLogger->flush();
        $jsonString = json_encode($logJson);
        assert($jsonString !== false);
        $logData = json_decode($jsonString, true);
        assert(is_array($logData));

        // Validate open context with Profile structure
        if (isset($logData['open'])) {
            $this->validateContextWithProfileSchema(
                $logData['open']['context'],
                __DIR__ . '/../docs/schema/open-context.json',
                'Open context with Profile structure',
            );
        }

        // Validate close context with Profile structure
        if (! isset($logData['close'])) {
            return;
        }

        $this->validateContextWithProfileSchema(
            $logData['close']['context'],
            __DIR__ . '/../docs/schema/complete-context.json',
            'Complete context with Profile structure',
        );
    }

    public function testVerboseErrorProfileSchemaValidation(): void
    {
        $testModule = new TestModule();
        $injector = new Injector($testModule);
        $resource = $injector->getInstance(ResourceInterface::class);

        // Generate error with Profile data
        try {
            $resource->get('app://self/error', ['id' => 'schema-test']);
            $this->fail('Expected exception was not thrown');
        } catch (Throwable) {
            // Expected exception
        }

        // Get the semantic log with error Profile structure
        $semanticLogger = $injector->getInstance(SemanticLoggerInterface::class);
        $logJson = $semanticLogger->flush();
        $jsonString = json_encode($logJson);
        assert($jsonString !== false);
        $logData = json_decode($jsonString, true);
        assert(is_array($logData));

        // Validate error context with Profile structure
        if (! isset($logData['close'])) {
            return;
        }

        $this->validateContextWithProfileSchema(
            $logData['close']['context'],
            __DIR__ . '/../docs/schema/error-context.json',
            'Error context with Profile structure',
        );
    }

    public function testVerboseProfileContextSchemaCompliance(): void
    {
        // Create actual Verbose Profile contexts directly
        $testModule = new TestModule();
        $injector = new Injector($testModule);
        $resource = $injector->getInstance(ResourceInterface::class);

        // Use actual resource call to generate proper AbstractRequest
        $resourceObject = $resource->get('app://self/simple', ['id' => 'verbose-schema-test']);
        $this->assertSame(200, $resourceObject->code);

        // Get semantic logger and flush to get the log data
        $semanticLogger = $injector->getInstance(SemanticLoggerInterface::class);
        $logJson = $semanticLogger->flush();
        $jsonString = json_encode($logJson);
        assert($jsonString !== false);
        $logData = json_decode($jsonString, true);
        assert(is_array($logData));

        // Validate the actual generated Profile contexts
        if (isset($logData['open']['context'])) {
            $this->validateContextWithProfileSchema(
                $logData['open']['context'],
                __DIR__ . '/../docs/schema/open-context.json',
                'Verbose OpenContext with Profile from actual resource call',
            );
        }

        if (! isset($logData['close']['context'])) {
            return;
        }

        $this->validateContextWithProfileSchema(
            $logData['close']['context'],
            __DIR__ . '/../docs/schema/complete-context.json',
            'Verbose CompleteContext with Profile from actual resource call',
        );
    }

    public function testVerboseErrorContextSchemaCompliance(): void
    {
        // Use actual error resource call to generate proper error context
        $testModule = new TestModule();
        $injector = new Injector($testModule);
        $resource = $injector->getInstance(ResourceInterface::class);

        try {
            $resource->get('app://self/error', ['id' => 'verbose-error-schema-test']);
            $this->fail('Expected exception was not thrown');
        } catch (Throwable) {
            // Expected exception - semantic logger should have captured it
        }

        // Get the semantic log with error Profile structure
        $semanticLogger = $injector->getInstance(SemanticLoggerInterface::class);
        $logJson = $semanticLogger->flush();
        $jsonString = json_encode($logJson);
        assert($jsonString !== false);
        $logData = json_decode($jsonString, true);
        assert(is_array($logData));

        // Validate the actual generated error Profile context
        if (! isset($logData['close']['context'])) {
            return;
        }

        $this->validateContextWithProfileSchema(
            $logData['close']['context'],
            __DIR__ . '/../docs/schema/error-context.json',
            'Verbose ErrorContext with Profile from actual error',
        );
    }

    /** @param array<string, mixed> $contextData */
    private function validateContextWithProfileSchema(array $contextData, string $schemaPath, string $testDescription): void
    {
        // Load schema
        $schemaContent = file_get_contents($schemaPath);
        assert($schemaContent !== false);
        $schema = json_decode($schemaContent);

        // Convert to JSON and back for proper validation format
        $contextJson = json_encode($contextData);
        assert($contextJson !== false);
        $contextForValidation = json_decode($contextJson);

        // Validate against schema
        $this->validator->validate($contextForValidation, $schema);

        if ($this->validator->isValid()) {
            $this->assertTrue(true, $testDescription . ' validates against schema');
        } else {
            $errors = [];
            foreach ($this->validator->getErrors() as $error) {
                $errors[] = sprintf('[%s] %s', $error['property'], $error['message']);
            }

            $this->fail($testDescription . ' schema validation failed: ' . implode(', ', $errors));
        }
    }
}
