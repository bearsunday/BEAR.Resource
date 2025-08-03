<?php

declare(strict_types=1);

namespace BEAR\Resource;

use BEAR\Resource\Fake\SemanticLogger\Module\TestModule;
use BEAR\Resource\SemanticLog\Profile\Verbose\CompleteContext;
use BEAR\Resource\SemanticLog\Profile\Verbose\OpenContext;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Override;
use PHPUnit\Framework\TestCase;
use Ray\Di\Injector;

use function assert;
use function dirname;
use function file_put_contents;
use function is_string;
use function json_encode;
use function mkdir;

use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;

final class UserListWithProblemsTest extends TestCase
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

    public function testUserListWithPerformanceProblems(): void
    {
        $resource = $this->resource->newInstance('app://self/user-list-with-problems');
        $request = new Request($this->invoker, $resource, 'GET', ['page' => 1]);

        $openContext = new OpenContext($request);
        $openId = $this->semanticLogger->open($openContext);

        // This will take ~200ms+ due to intentional performance issues
        $resource = $this->resource->get('app://self/user-list-with-problems', ['page' => 1]);
        $completeContext = new CompleteContext($resource, $openContext);

        $this->semanticLogger->close($completeContext, $openId);

        // Get the semantic log output with profiling data
        $logJson = $this->semanticLogger->flush();
        $jsonString = json_encode($logJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        assert(is_string($jsonString));

        // Save semantic log for AI analysis
        $outputFile = __DIR__ . '/tmp/UserListWithProblemsTest/testUserListWithPerformanceProblems.json';
        @mkdir(dirname($outputFile), 0755, true);
        file_put_contents($outputFile, $jsonString);

        // Verify the resource executed successfully
        $this->assertSame(200, $resource->code);
        $this->assertIsArray($resource->body);
        $this->assertArrayHasKey('users', $resource->body);
        $this->assertCount(20, $resource->body['users']);

        // This should have real performance problems for AI to analyze
        $this->assertTrue(true, 'Performance problems test completed - check the generated profile files!');
    }
}
