<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Exception\ResourceNotFoundException;
use BEAR\Resource\Fake\SemanticLogger\Module\TestModule;
use BEAR\Resource\SemanticLog\Profile\Profile;
use BEAR\Resource\SemanticLog\Profile\Verbose\CompleteContext;
use BEAR\Resource\SemanticLog\Profile\Verbose\ContextFactory;
use BEAR\Resource\SemanticLog\Profile\Verbose\ErrorContext;
use BEAR\Resource\SemanticLog\Profile\Verbose\OpenContext;
use BEAR\Resource\SemanticLog\SemanticInvoker;
use DomainException;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Override;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;
use RuntimeException;

use function assert;
use function dirname;
use function extension_loaded;
use function file_exists;
use function file_put_contents;
use function filesize;
use function function_exists;
use function is_file;
use function is_string;
use function json_encode;
use function mkdir;
use function str_ends_with;
use function unlink;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

final class SemanticLogVerboseProfileTest extends TestCase
{
    private ResourceInterface $resource;
    private InvokerInterface $invoker;
    private SemanticLoggerInterface $semanticLogger;

    #[Override]
    protected function setUp(): void
    {
        $injector = new Injector(new TestModule());
        $this->resource = $injector->getInstance(ResourceInterface::class);
        $this->invoker = $injector->getInstance(InvokerInterface::class);
        $this->semanticLogger = $injector->getInstance(SemanticLoggerInterface::class);
    }

    public function testVerboseOpenContextHasProfilingProperties(): void
    {
        $resource = $this->resource->newInstance('app://self/simple');
        $request = new Request($this->invoker, $resource, 'GET', ['id' => 'test']);

        $openContext = new OpenContext($request);

        $this->assertSame('GET', $openContext->method);
        $this->assertSame('app://self/simple?id=test', $openContext->uri);

        // XdebugId should always be available (used as profiling session ID)
        $xdebugId = $openContext->getXdebugId();
        $this->assertIsString($xdebugId);
        $this->assertStringStartsWith('profile_', $xdebugId);
    }

    public function testVerboseCompleteContextHasProfilingFields(): void
    {
        $resource = $this->resource->newInstance('app://self/simple');
        $request = new Request($this->invoker, $resource, 'GET', ['id' => 'test']);

        $openContext = new OpenContext($request);
        $resource = $this->resource->get('app://self/simple', ['id' => 'test']);

        $completeContext = new CompleteContext($resource, $openContext);

        $this->assertSame(200, $completeContext->code);
        $this->assertIsString($completeContext->uri);
        $this->assertObjectHasProperty('profile', $completeContext);

        // Test profiling structure exists
        $this->assertInstanceOf(Profile::class, $completeContext->profile);

        // Test profiling files (may be null if extensions not available)
        if (! function_exists('xhprof_disable')) {
            return;
        }

        $xhprofFile = $completeContext->profile->xhprof?->file;
        $this->assertTrue(
            $xhprofFile === null || is_file($xhprofFile),
        );
    }

    public function testVerboseErrorContextHasProfilingFields(): void
    {
        $resource = $this->resource->newInstance('app://self/simple');
        $request = new Request($this->invoker, $resource, 'GET', ['id' => 'test']);

        $openContext = new OpenContext($request);
        $exception = new RuntimeException('Test exception');

        $errorContext = new ErrorContext($exception, 'test-error', $openContext);

        $this->assertSame('test-error', $errorContext->exceptionId);
        $this->assertStringContainsString('Test exception', $errorContext->exceptionAsString);
        $this->assertObjectHasProperty('profile', $errorContext);
        $this->assertInstanceOf(Profile::class, $errorContext->profile);
    }

    public function testXhprofIntegrationWhenAvailable(): void
    {
        if (! function_exists('xhprof_enable') || ! function_exists('xhprof_disable')) {
            $this->markTestSkipped('XHProf extension not available');
        }

        $resource = $this->resource->newInstance('app://self/simple');
        $request = new Request($this->invoker, $resource, 'GET', ['id' => 'xhprof-test']);

        $openContext = new OpenContext($request);
        $openId = $this->semanticLogger->open($openContext);

        $resource = $this->resource->get('app://self/simple', ['id' => 'xhprof-test']);
        $completeContext = new CompleteContext($resource, $openContext);

        $this->semanticLogger->close($completeContext, $openId);

        // Get the actual semantic log output with XHProf profiling data
        $logJson = $this->semanticLogger->flush();
        $jsonString = json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        assert(is_string($jsonString));

        // Save XHProf integration semantic log for verification
        $outputFile = __DIR__ . '/tmp/SemanticLogVerboseProfileTest/testXhprofIntegrationWhenAvailable.json';
        @mkdir(dirname($outputFile), 0755, true);
        file_put_contents($outputFile, $jsonString);

        // Verify XHProf profiling data in semantic log
        $this->assertStringContainsString('"uri": "app://self/simple?id=xhprof-test"', $jsonString);
        $this->assertStringContainsString('"method": "GET"', $jsonString);
        $this->assertStringContainsString('"id": "xhprof-test"', $jsonString);
        $this->assertStringNotContainsString('"xhdebugId":', $jsonString); // Should NOT be exposed
        $this->assertStringContainsString('"file":', $jsonString);

        // Verify XHProf file was created if profiling was enabled
        $xhprofFile = $completeContext->profile->xhprof?->file;
        if ($xhprofFile !== null && file_exists($xhprofFile)) {
            $this->assertIsString($xhprofFile);
            $this->assertStringContainsString('xhprof_', $xhprofFile);
            $this->assertGreaterThan(0, filesize($xhprofFile));

            // Clean up
            unlink($xhprofFile);
        }

        $this->assertTrue(true, 'XHProf integration test completed');
    }

    public function testXdebugIntegrationWhenAvailable(): void
    {
        if (! extension_loaded('xdebug') || ! function_exists('xdebug_start_trace')) {
            $this->markTestSkipped('Xdebug extension not available or trace not enabled');
        }

        $resource = $this->resource->newInstance('app://self/simple');
        $request = new Request($this->invoker, $resource, 'GET', ['id' => 'xdebug-test']);

        $openContext = new OpenContext($request);
        $openId = $this->semanticLogger->open($openContext);

        $resource = $this->resource->get('app://self/simple', ['id' => 'xdebug-test']);
        $completeContext = new CompleteContext($resource, $openContext);

        $this->semanticLogger->close($completeContext, $openId);

        // Get the actual semantic log output with Xdebug tracing data
        $logJson = $this->semanticLogger->flush();
        $jsonString = json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        assert(is_string($jsonString));

        // Save Xdebug integration semantic log for verification
        $outputFile = __DIR__ . '/tmp/SemanticLogVerboseProfileTest/testXdebugIntegrationWhenAvailable.json';
        @mkdir(dirname($outputFile), 0755, true);
        file_put_contents($outputFile, $jsonString);

        // Verify Xdebug tracing data in semantic log
        $this->assertStringContainsString('"uri": "app://self/simple?id=xdebug-test"', $jsonString);
        $this->assertStringContainsString('"method": "GET"', $jsonString);
        $this->assertStringContainsString('"id": "xdebug-test"', $jsonString);
        $this->assertStringNotContainsString('"xhdebugId":', $jsonString); // Should NOT be exposed
        $this->assertStringContainsString('"profile":', $jsonString);

        // Verify Xdebug trace file was created if tracing was enabled
        $xdebugFile = $completeContext->profile->xdebug?->file;
        if ($xdebugFile !== null && file_exists($xdebugFile)) {
            $this->assertIsString($xdebugFile);
            // Xdebug can create either .xt (uncompressed) or .xt.gz (compressed) files
            $this->assertTrue(
                str_ends_with($xdebugFile, '.xt') ||
                str_ends_with($xdebugFile, '.xt.gz'),
                'Xdebug trace file should end with .xt or .xt.gz',
            );

            // Clean up
            unlink($xdebugFile);
        }

        $this->assertTrue(true, 'Xdebug integration test completed');
    }

    public function testSemanticInvokerErrorHandling(): void
    {
        // Verify that SemanticInvoker is bound correctly
        $this->assertInstanceOf(SemanticInvoker::class, $this->invoker);

        // Test SemanticInvoker's error handling (catch block)
        $this->expectException(ResourceNotFoundException::class);

        // This should trigger SemanticInvoker's error handling path
        $this->resource->get('app://self/nonexistent', ['id' => 'error-test']);
    }

    public function testVerboseContextMethods(): void
    {
        // Test missing methods for 100% coverage
        $resource = $this->resource->newInstance('app://self/simple');
        $request = new Request($this->invoker, $resource, 'GET', ['id' => 'coverage-test']);

        // Test Verbose OpenContext methods
        $openContext = new OpenContext($request);
        $this->assertSame('GET', $openContext->method);
        $this->assertSame('app://self/simple?id=coverage-test', $openContext->uri);

        // Test static create method
        $openContext2 = OpenContext::create($request);
        $this->assertInstanceOf(OpenContext::class, $openContext2);

        // Test getXdebugId method
        $xdebugId = $openContext->getXdebugId();
        $this->assertIsString($xdebugId);

        // Test jsonSerialize
        $openSerialized = $openContext->jsonSerialize();
        $this->assertIsArray($openSerialized);
        $this->assertArrayHasKey('method', $openSerialized);
        $this->assertArrayHasKey('uri', $openSerialized);

        // Test Verbose CompleteContext
        $resourceObject = $this->resource->get('app://self/simple', ['id' => 'coverage-test']);
        $completeContext = new CompleteContext($resourceObject, $openContext);

        // Test static create method
        $completeContext2 = CompleteContext::create($resourceObject, $openContext);
        $this->assertInstanceOf(CompleteContext::class, $completeContext2);

        // Test jsonSerialize
        $completeSerialized = $completeContext->jsonSerialize();
        $this->assertIsArray($completeSerialized);
        $this->assertArrayHasKey('uri', $completeSerialized);
        $this->assertArrayHasKey('code', $completeSerialized);

        // Test Verbose ErrorContext with auto-generated ID
        $exception = new RuntimeException('Auto ID test');
        $errorContext = new ErrorContext($exception, '', $openContext);
        $this->assertStringStartsWith('e-bear-resource-', $errorContext->exceptionId);

        // Test static create method
        $errorContext2 = ErrorContext::create($exception, 'manual-id', $openContext);
        $this->assertInstanceOf(ErrorContext::class, $errorContext2);
        $this->assertSame('manual-id', $errorContext2->exceptionId);

        // Test jsonSerialize
        $errorSerialized = $errorContext->jsonSerialize();
        $this->assertIsArray($errorSerialized);
        $this->assertArrayHasKey('exceptionId', $errorSerialized);
        $this->assertArrayHasKey('exceptionAsString', $errorSerialized);

        // Test Verbose ContextFactory
        $factory = new ContextFactory();
        $factoryOpen = $factory->createOpenContext($request);
        $this->assertInstanceOf(OpenContext::class, $factoryOpen);

        $factoryComplete = $factory->createCompleteContext($resourceObject, $openContext);
        $this->assertInstanceOf(CompleteContext::class, $factoryComplete);

        $factoryError = $factory->createErrorContext($exception, 'factory-error', $openContext);
        $this->assertInstanceOf(ErrorContext::class, $factoryError);

        // Test ErrorContext createExceptionId method (private method coverage)
        $errorWithAutoId = new ErrorContext($exception, '', null);
        $this->assertStringStartsWith('e-bear-resource-', $errorWithAutoId->exceptionId);

        // Test Verbose CompleteContext static create method (missing coverage)
        $staticCompleteContext = CompleteContext::create($resourceObject, $openContext);
        $this->assertInstanceOf(CompleteContext::class, $staticCompleteContext);

        // Test Verbose CompleteContext jsonSerialize method (missing coverage)
        $completeJsonData = $staticCompleteContext->jsonSerialize();
        $this->assertIsArray($completeJsonData);
        $this->assertArrayHasKey('uri', $completeJsonData);
        $this->assertArrayHasKey('code', $completeJsonData);
        $this->assertArrayHasKey('headers', $completeJsonData);
        $this->assertArrayHasKey('body', $completeJsonData);
        $this->assertArrayHasKey('view', $completeJsonData);

        // Test Verbose ErrorContext static create method (missing coverage)
        $staticErrorContext = ErrorContext::create($exception, 'static-error-id', $openContext);
        $this->assertInstanceOf(ErrorContext::class, $staticErrorContext);
        $this->assertSame('static-error-id', $staticErrorContext->exceptionId);

        // Test Verbose ErrorContext jsonSerialize method (missing coverage)
        $errorJsonData = $staticErrorContext->jsonSerialize();
        $this->assertIsArray($errorJsonData);
        $this->assertArrayHasKey('exceptionId', $errorJsonData);
        $this->assertArrayHasKey('exceptionAsString', $errorJsonData);

        // Test Verbose OpenContext static create method (missing coverage)
        $staticOpenContext = OpenContext::create($request);
        $this->assertInstanceOf(OpenContext::class, $staticOpenContext);

        // Test Verbose OpenContext jsonSerialize method (missing coverage)
        $openJsonData = $staticOpenContext->jsonSerialize();
        $this->assertIsArray($openJsonData);
        $this->assertArrayHasKey('method', $openJsonData);
        $this->assertArrayHasKey('uri', $openJsonData);
        $this->assertSame('GET', $openJsonData['method']);
        $this->assertIsString($openJsonData['uri']);
        $this->assertStringContainsString('app://self/simple', $openJsonData['uri']);
    }

    public function testSemanticInvokerDirectUsage(): void
    {
        // Direct test of SemanticInvoker to achieve 100% coverage
        $resource = $this->resource->newInstance('app://self/simple');
        $request = new Request($this->invoker, $resource, 'GET', ['id' => 'invoker-test']);

        // Get the actual SemanticInvoker instance
        $semanticInvoker = $this->invoker;
        $this->assertInstanceOf(SemanticInvoker::class, $semanticInvoker);

        // Test invoke method directly
        $result = $semanticInvoker->invoke($request);
        $this->assertInstanceOf(ResourceObject::class, $result);
        $this->assertSame(200, $result->code);
    }

    public function testSemanticInvokerErrorPath(): void
    {
        // Test SemanticInvoker error handling path for complete coverage
        $semanticInvoker = $this->invoker;
        $this->assertInstanceOf(SemanticInvoker::class, $semanticInvoker);

        // Use existing Error resource that definitely throws exceptions
        $resource = $this->resource->newInstance('app://self/error');
        $request = new Request($this->invoker, $resource, 'GET', ['type' => 'runtime']);

        // Directly call SemanticInvoker.invoke() to test the catch block
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('This is a test runtime exception');

        // This should trigger the catch (Throwable $e) block in SemanticInvoker
        $semanticInvoker->invoke($request);
    }

    public function testSemanticInvokerCatchBlockCoverage(): void
    {
        // Additional test to ensure catch block coverage with proper error context creation
        $semanticInvoker = $this->invoker;
        $this->assertInstanceOf(SemanticInvoker::class, $semanticInvoker);

        // Test that SemanticInvoker properly handles exceptions and creates error contexts
        try {
            $resource = $this->resource->newInstance('app://self/error');
            $request = new Request($this->invoker, $resource, 'GET', ['type' => 'domain']);
            $semanticInvoker->invoke($request);
            $this->fail('Expected exception was not thrown');
        } catch (DomainException $e) {
            // Verify exception was properly re-thrown after logging
            $this->assertStringContainsString('Domain logic error occurred', $e->getMessage());
        }
    }
}
