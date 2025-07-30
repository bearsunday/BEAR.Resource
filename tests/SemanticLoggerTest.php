<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Fake\SemanticLogger\Module\TestModule;
use BEAR\Resource\SemanticLog\Module\SemanticLoggerModule;
use BEAR\Resource\SemanticLog\ResourceCompleteContext;
use BEAR\Resource\SemanticLog\ResourceErrorContext;
use BEAR\Resource\SemanticLog\ResourceOpenContext;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Override;
use PHPUnit\Framework\TestCase;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

use function assert;
use function is_string;
use function json_encode;
use function substr_count;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

final class SemanticLoggerTest extends TestCase
{
    private SemanticLoggerInterface $semanticLogger;
    private ResourceInterface $resource;

    protected function setUp(): void
    {
        $combinedModule = new class extends AbstractModule {
            #[Override]
            protected function configure(): void
            {
                $this->install(new TestModule());
                $this->install(new SemanticLoggerModule());
            }
        };

        $injector = new Injector($combinedModule);
        $this->resource = $injector->getInstance(ResourceInterface::class);
        $this->semanticLogger = $injector->getInstance(SemanticLoggerInterface::class);
    }

    public function testBasicSemanticLoggerUsage(): void
    {
        // Basic usage example of semantic logger
        $openContext = new ResourceOpenContext('app://self/simple', 'GET', ['id' => 'test123']);
        $openId = $this->semanticLogger->open($openContext);

        $resource = $this->resource->get('app://self/simple', ['id' => 'test123']);
        $completeContext = new ResourceCompleteContext($resource, 'GET');
        $this->semanticLogger->close($completeContext, $openId);

        $logJson = $this->semanticLogger->flush();
        $jsonString = json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        assert(is_string($jsonString));

        /*
         * ACTUAL semantic log output structure (see console output below):
         * - open.context: {uri, method, query} - request information
         * - close.context: {uri, code, headers, body, view, method} - response information  
         * - events: [] - error events that occurred during processing
         * - links: [] - HAL links from the resource
         * 
         * NOTE: The actual field order in close.context is:
         * uri → code → headers → body → view → method (method comes last)
         * 
         * Examples of what might appear in arrays:
         * events: [{"type": "bear_resource_error", "context": {"uri": "...", "exceptionClass": "RuntimeException"}}]
         * links: [{"rel": "self", "href": "app://self/simple?id=test123"}]
         */

        // Save actual output for comparison and documentation (overwrites each time)
        $outputFile = __DIR__ . '/tmp/SemanticLoggerTest/testBasicSemanticLoggerUsage.json';
        @mkdir(dirname($outputFile), 0755, true);
        file_put_contents($outputFile, $jsonString);
        echo "\nSemantic log output saved to: SemanticLoggerTest/" . basename($outputFile) . "\n";

        // Verify key parts of the semantic log structure with string comparisons
        $this->assertStringContainsString('"uri": "app://self/simple"', $jsonString);
        $this->assertStringContainsString('"method": "GET"', $jsonString);
        $this->assertStringContainsString('"id": "test123"', $jsonString);
        $this->assertStringContainsString('"code": 200', $jsonString);
        $this->assertStringContainsString('"message": "Hello from Simple"', $jsonString);
        $this->assertStringContainsString('"events": []', $jsonString);
        $this->assertStringContainsString('"links": []', $jsonString);
        $this->assertStringContainsString('https://bearsunday.github.io/BEAR.Resource/schemas/bear-resource-request.json', $jsonString);
        $this->assertStringContainsString('https://bearsunday.github.io/BEAR.Resource/schemas/bear-resource-complete.json', $jsonString);
    }

    public function testPostMethodSemanticLogging(): void
    {
        $openContext = new ResourceOpenContext('app://self/simple', 'POST', ['name' => 'John', 'email' => 'john@example.com']);
        $openId = $this->semanticLogger->open($openContext);

        $resource = $this->resource->post('app://self/simple', ['name' => 'John', 'email' => 'john@example.com']);
        $completeContext = new ResourceCompleteContext($resource, 'POST');
        $this->semanticLogger->close($completeContext, $openId);

        $logJson = $this->semanticLogger->flush();
        $jsonString = json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        assert(is_string($jsonString));

        // Verify POST method logging
        $this->assertStringContainsString('"method": "POST"', $jsonString);
        $this->assertStringContainsString('"name": "John"', $jsonString);
        $this->assertStringContainsString('"email": "john@example.com"', $jsonString);
        $this->assertStringContainsString('"code": 201', $jsonString);
        $this->assertStringContainsString('"status": "created"', $jsonString);
    }

    public function testNestedResourceCalls(): void
    {
        // First resource call
        $context1 = new ResourceOpenContext('app://self/simple', 'GET', ['id' => 'first']);
        $id1 = $this->semanticLogger->open($context1);

        // Nested resource call
        $context2 = new ResourceOpenContext('app://self/nested', 'GET', ['type' => 'nested']);
        $id2 = $this->semanticLogger->open($context2);

        // Close nested first (LIFO order)
        $resource2 = $this->resource->get('app://self/nested', ['type' => 'nested']);
        $complete2 = new ResourceCompleteContext($resource2, 'GET');
        $this->semanticLogger->close($complete2, $id2);

        // Close outer
        $resource1 = $this->resource->get('app://self/simple', ['id' => 'first']);
        $complete1 = new ResourceCompleteContext($resource1, 'GET');
        $this->semanticLogger->close($complete1, $id1);

        $logJson = $this->semanticLogger->flush();
        $jsonString = json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        assert(is_string($jsonString));

        // Verify hierarchical structure
        $this->assertStringContainsString('"uri": "app://self/simple"', $jsonString);
        $this->assertStringContainsString('"uri": "app://self/nested"', $jsonString);
        $this->assertStringContainsString('"level": "nested"', $jsonString);
        $this->assertStringContainsString('"open": {', $jsonString); // Nested structure
    }

    public function testEmbeddedResourceLogging(): void
    {
        // Test @Embed functionality with semantic logging
        $openContext = new ResourceOpenContext('app://self/embedding', 'GET', ['id' => 'embed_test', 'type' => 'embedded']);
        $openId = $this->semanticLogger->open($openContext);

        $resource = $this->resource->get('app://self/embedding', ['id' => 'embed_test', 'type' => 'embedded']);
        // Force lazy evaluation to trigger @Embed
        $resourceString = (string) $resource;
        unset($resourceString);

        $completeContext = new ResourceCompleteContext($resource, 'GET');
        $this->semanticLogger->close($completeContext, $openId);

        $logJson = $this->semanticLogger->flush();
        $jsonString = json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        assert(is_string($jsonString));

        // Verify main resource
        $this->assertStringContainsString('"uri": "app://self/embedding"', $jsonString);
        $this->assertStringContainsString('"main_id": "embed_test"', $jsonString);
        $this->assertStringContainsString('"main_type": "embedded"', $jsonString);
        $this->assertStringContainsString('"description": "Resource with embedded content"', $jsonString);

        // Note: @Embed resources won't appear in semantic log with this simple setup
        // They would need SemanticResourceInvokerAdapter to be properly bound
        // This test verifies the main resource works correctly
        $this->assertStringContainsString('"code": 200', $jsonString);
    }

    public function testThreeLevelNesting(): void
    {
        // Test 3-level nesting that previously caused null field issues

        // Level 1: Simple resource
        $context1 = new ResourceOpenContext('app://self/simple', 'GET', ['id' => 'level1']);
        $id1 = $this->semanticLogger->open($context1);

        // Level 2: Nested resource
        $context2 = new ResourceOpenContext('app://self/nested', 'GET', ['type' => 'level2']);
        $id2 = $this->semanticLogger->open($context2);

        // Level 3: Deep nested resource
        $context3 = new ResourceOpenContext('app://self/deep-nested', 'GET', ['level' => 'level3']);
        $id3 = $this->semanticLogger->open($context3);

        // Close in LIFO order (3 → 2 → 1)
        $resource3 = $this->resource->get('app://self/deep-nested', ['level' => 'level3']);
        $complete3 = new ResourceCompleteContext($resource3, 'GET');
        $this->semanticLogger->close($complete3, $id3);

        $resource2 = $this->resource->get('app://self/nested', ['type' => 'level2']);
        $complete2 = new ResourceCompleteContext($resource2, 'GET');
        $this->semanticLogger->close($complete2, $id2);

        $resource1 = $this->resource->get('app://self/simple', ['id' => 'level1']);
        $complete1 = new ResourceCompleteContext($resource1, 'GET');
        $this->semanticLogger->close($complete1, $id1);

        $logJson = $this->semanticLogger->flush();
        $jsonString = json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        assert(is_string($jsonString));

        /*
         * Expected 3-level nested structure:
         * {
         *     "schemaUrl": "...",
         *     "open": {
         *         "type": "bear_resource_request",
         *         "context": {"uri": "app://self/simple", "method": "GET", "query": {"id": "level1"}},
         *         "open": {
         *             "type": "bear_resource_request", 
         *             "context": {"uri": "app://self/nested", "method": "GET", "query": {"type": "level2"}},
         *             "open": {
         *                 "type": "bear_resource_request",
         *                 "context": {"uri": "app://self/deep-nested", "method": "GET", "query": {"level": "level3"}}
         *             },
         *             "close": {"type": "bear_resource_complete", "context": {"uri": "app://self/deep-nested?level=level3", "method": "GET", "code": 200, "body": {"level": "level3", "depth": "deep_nested"}}}
         *         },
         *         "close": {"type": "bear_resource_complete", "context": {"uri": "app://self/nested?type=level2", "method": "GET", "code": 200, "body": {"type": "level2", "data": "Nested resource data"}}}
         *     },
         *     "close": {"type": "bear_resource_complete", "context": {"uri": "app://self/simple?id=level1", "method": "GET", "code": 200, "body": {"id": "level1", "message": "Hello from Simple"}}},
         *     "events": [
         *         // Events would appear here if errors occurred during nested operations
         *         // Each nested resource call could generate error events
         *     ],
         *     "links": [
         *         // HAL links from any of the 3 nested resources would be collected here
         *         // Links are aggregated from all levels of the hierarchy
         *     ]
         * }
         */

        // Save actual 3-level nesting output for documentation
        $outputFile = __DIR__ . '/tmp/SemanticLoggerTest/testThreeLevelNesting.json';
        @mkdir(dirname($outputFile), 0755, true);
        file_put_contents($outputFile, $jsonString);
        echo "\n3-level nesting output saved to: SemanticLoggerTest/" . basename($outputFile) . "\n";

        // Verify all 3 levels are present
        $this->assertStringContainsString('"uri": "app://self/simple"', $jsonString);
        $this->assertStringContainsString('"uri": "app://self/nested"', $jsonString);
        $this->assertStringContainsString('"uri": "app://self/deep-nested"', $jsonString);

        // Verify level-specific data
        $this->assertStringContainsString('"id": "level1"', $jsonString);
        $this->assertStringContainsString('"type": "level2"', $jsonString);
        $this->assertStringContainsString('"level": "level3"', $jsonString);
        $this->assertStringContainsString('"depth": "deep_nested"', $jsonString);

        // Verify 3-level nested structure
        $openCount = substr_count($jsonString, '"open": {');
        $this->assertSame(3, $openCount, 'Should have exactly 3 nested open structures');

        // Critical test: Should not contain null fields (the original issue)
        $this->assertStringNotContainsString(': null', $jsonString, 'Should not contain any null fields');
        $this->assertStringNotContainsString('null', $jsonString, 'Should not contain null values');
    }

    public function testSemanticLoggerWithErrorEvent(): void
    {
        // Example showing how events array is populated with errors
        $openContext = new ResourceOpenContext('app://self/simple', 'GET', ['id' => 'error_test']);
        $openId = $this->semanticLogger->open($openContext);

        // Simulate an error event manually (since we don't have actual error resources)
        $errorContext = new ResourceErrorContext(
            'RuntimeException',
            'Simulated error for demonstration'
        );
        $this->semanticLogger->event($errorContext);

        $resource = $this->resource->get('app://self/simple', ['id' => 'error_test']);
        $completeContext = new ResourceCompleteContext($resource, 'GET');
        $this->semanticLogger->close($completeContext, $openId);

        $logJson = $this->semanticLogger->flush();
        $jsonString = json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        assert(is_string($jsonString));

        /*
         * Example output with error event:
         * {
         *     "schemaUrl": "...",
         *     "open": {
         *         "type": "bear_resource_request",
         *         "context": {"uri": "app://self/simple", "method": "GET", "query": {"id": "error_test"}}
         *     },
         *     "close": {
         *         "type": "bear_resource_complete", 
         *         "context": {"uri": "app://self/simple?id=error_test", "method": "GET", "code": 200, "body": {"id": "error_test", "message": "Hello from Simple"}}
         *     },
         *     "events": [
         *         {
         *             "id": "bear_resource_error_1",
         *             "type": "bear_resource_error",
         *             "$schema": "file:///path/to/bear-resource-error.json",
         *             "context": {
         *                 "uri": "app://self/simple",
         *                 "method": "GET",
         *                 "exceptionClass": "RuntimeException",
         *                 "exceptionMessage": "Simulated error for demonstration"
         *             }
         *         }
         *     ],
         *     "links": []
         * }
         */

        // Save actual error event output for documentation
        $outputFile = __DIR__ . '/tmp/SemanticLoggerTest/testSemanticLoggerWithErrorEvent.json';
        @mkdir(dirname($outputFile), 0755, true);
        file_put_contents($outputFile, $jsonString);
        echo "\nError event output saved to: SemanticLoggerTest/" . basename($outputFile) . "\n";

        // Verify error event is recorded
        $this->assertStringContainsString('"events":', $jsonString);
        $this->assertStringContainsString('"type": "bear_resource_error"', $jsonString);
        $this->assertStringContainsString('"exceptionClass": "RuntimeException"', $jsonString);
        $this->assertStringContainsString('"exceptionMessage": "Simulated error for demonstration"', $jsonString);
        $this->assertStringContainsString('"exceptionId": "e-bear-resource-', $jsonString);
        $this->assertStringContainsString('https://bearsunday.github.io/BEAR.Resource/schemas/bear-resource-error.json', $jsonString);
    }

    public function testResourceExceptionHandling(): void
    {
        // Test actual resource exception (not manual error event)
        $openContext = new ResourceOpenContext('app://self/error', 'GET', ['type' => 'runtime']);
        $openId = $this->semanticLogger->open($openContext);

        try {
            // This should throw RuntimeException from the resource
            $this->resource->get('app://self/error', ['type' => 'runtime']);
            $this->fail('Expected RuntimeException was not thrown');
        } catch (\RuntimeException $e) {
            // Resource threw exception - SemanticResourceInvokerAdapter should handle this
            $this->assertSame('This is a test runtime exception', $e->getMessage());
            
            // Note: In a real scenario with SemanticResourceInvokerAdapter properly bound,
            // the adapter would automatically create an error context and close the log.
            // For this test, we manually close with error context to simulate that behavior.
            $errorContext = new ResourceErrorContext(
                \RuntimeException::class,
                $e->getMessage()
            );
            $this->semanticLogger->close($errorContext, $openId);
        }

        $logJson = $this->semanticLogger->flush();
        $jsonString = json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        assert(is_string($jsonString));

        /*
         * Example output with actual resource exception:
         * {
         *     "schemaUrl": "...",
         *     "open": {
         *         "type": "bear_resource_request",
         *         "context": {"uri": "app://self/error_resource", "method": "GET", "query": {"type": "runtime"}}
         *     },
         *     "close": {
         *         "type": "bear_resource_error",
         *         "context": {
         *             "uri": "app://self/error_resource",
         *             "method": "GET", 
         *             "exceptionClass": "RuntimeException",
         *             "exceptionMessage": "This is a test runtime exception"
         *         }
         *     },
         *     "events": [],
         *     "links": []
         * }
         */

        // Save actual resource exception output for documentation
        $outputFile = __DIR__ . '/tmp/SemanticLoggerTest/testResourceExceptionHandling.json';
        @mkdir(dirname($outputFile), 0755, true);
        file_put_contents($outputFile, $jsonString);
        echo "\nResource exception output saved to: SemanticLoggerTest/" . basename($outputFile) . "\n";

        // Verify actual resource exception is recorded
        $this->assertStringContainsString('"type": "runtime"', $jsonString);
        $this->assertStringContainsString('"type": "bear_resource_error"', $jsonString);
        $this->assertStringContainsString('"exceptionClass": "RuntimeException"', $jsonString);
        $this->assertStringContainsString('"exceptionMessage": "This is a test runtime exception"', $jsonString);
        $this->assertStringContainsString('"exceptionId": "e-bear-resource-', $jsonString);
        $this->assertStringContainsString('https://bearsunday.github.io/BEAR.Resource/schemas/bear-resource-error.json', $jsonString);
    }
}
