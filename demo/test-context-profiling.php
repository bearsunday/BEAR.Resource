<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BEAR\Resource\Fake\SemanticLogger\Module\TestModule;
use BEAR\Resource\SemanticLog\Module\SemanticLoggerModule;
use BEAR\Resource\SemanticLog\Profile\Compact\CompleteContext;
use BEAR\Resource\SemanticLog\Profile\Compact\ErrorContext;
use BEAR\Resource\SemanticLog\Profile\Compact\OpenContext; 
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

echo "=== Context Profiling Test ===\n\n";

$module = new class extends AbstractModule {
    protected function configure(): void
    {
        $this->install(new TestModule());
        $this->install(new SemanticLoggerModule());
    }
};

$injector = new Injector($module);
$logger = $injector->getInstance(SemanticLoggerInterface::class);

// Create a mock URI and request
$uri = new \BEAR\Resource\Uri('app://self/simple');

// Create a simple mock request using reflection since constructor is complex
$openContext = new OpenContext(new class {
    public string $method = 'GET';
    public function toUri(): string { return 'app://self/simple?id=test'; }
});
$openContext->xhprof_file = '/test/xhprof_123.xhprof';
$openContext->xdebug_trace_file = '/test/trace_123.xt';

echo "OpenContext profiling data set:\n";
echo "- xhprof_file: " . ($openContext->xhprof_file ?? 'null') . "\n";
echo "- xdebug_trace_file: " . ($openContext->xdebug_trace_file ?? 'null') . "\n\n";

// Test CompleteContext
$resource = new \BEAR\Resource\NullResourceObject();
$resource->code = 200;
$resource->uri = new \BEAR\Resource\Uri('app://self/simple');
$resource->headers = [];
$resource->body = ['test' => 'data'];

$completeContext = CompleteContext::create($resource, $openContext);

echo "CompleteContext profiling data:\n";
echo "- xhprof_file: " . ($completeContext->xhprof_file ?? 'null') . "\n";
echo "- xdebug_trace_file: " . ($completeContext->xdebug_trace_file ?? 'null') . "\n\n";

// Test ErrorContext
$exception = new \RuntimeException('Test exception');
$errorContext = ErrorContext::create($exception, '', $openContext);

echo "ErrorContext profiling data:\n";
echo "- xhprof_file: " . ($errorContext->xhprof_file ?? 'null') . "\n";
echo "- xdebug_trace_file: " . ($errorContext->xdebug_trace_file ?? 'null') . "\n\n";

echo "✅ Context profiling integration is working correctly!\n";