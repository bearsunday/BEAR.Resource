<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BEAR\Resource\Fake\SemanticLogger\Module\TestModule;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\SemanticLog\Module\SemanticLoggerModule;
use BEAR\Resource\SemanticLog\SemanticInvoker;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

echo "=== Semantic Invoker Debug ===\n\n";

$combinedModule = new class extends AbstractModule {
    protected function configure(): void
    {
        $this->install(new TestModule());
        $this->install(new SemanticLoggerModule());
    }
};

$injector = new Injector($combinedModule);

// Check what invoker is bound
$invoker = $injector->getInstance(InvokerInterface::class);
echo "Invoker class: " . get_class($invoker) . "\n";

if ($invoker instanceof SemanticInvoker) {
    echo "✅ SemanticInvoker is bound correctly\n";
} else {
    echo "❌ SemanticInvoker is NOT bound - using: " . get_class($invoker) . "\n";
}

$resource = $injector->getInstance(ResourceInterface::class);
echo "Resource class: " . get_class($resource) . "\n";

echo "\nMaking resource request to test invoker...\n";

// Add some debug output to see if our code is called
class DebugSemanticInvoker extends SemanticInvoker {
    public function invoke(\BEAR\Resource\AbstractRequest $request): \BEAR\Resource\ResourceObject
    {
        echo "🔍 DEBUG: SemanticInvoker.invoke() called for URI: " . $request->uri . "\n";
        return parent::invoke($request);
    }
}

// Try to override with debug version
$debugModule = new class extends AbstractModule {
    protected function configure(): void
    {
        $this->install(new TestModule());
        $this->install(new SemanticLoggerModule());
        // This won't work easily because of the Named annotation, but let's try anyway
    }
};

echo "\nExecuting resource call...\n";
$result = $resource->get('app://self/simple', ['id' => 'debug-test']);
echo "Result code: " . $result->code . "\n";
echo "Result ID: " . $result->body['id'] . "\n";

// Check if any files were created
$xdebugDir = __DIR__ . '/../tests/tmp/xdebug';
$xhprofDir = __DIR__ . '/../tests/tmp/xhprof';

echo "\nFile system check:\n";
echo "Xdebug dir exists: " . (is_dir($xdebugDir) ? 'Yes' : 'No') . "\n";
echo "XHProf dir exists: " . (is_dir($xhprofDir) ? 'Yes' : 'No') . "\n";

if (is_dir($xdebugDir)) {
    $xdebugFiles = glob($xdebugDir . '/*');
    echo "Xdebug files: " . count($xdebugFiles) . "\n";
    foreach ($xdebugFiles as $file) {
        echo "  - " . basename($file) . "\n";
    }
}

if (is_dir($xhprofDir)) {
    $xhprofFiles = glob($xhprofDir . '/*');
    echo "XHProf files: " . count($xhprofFiles) . "\n";
    foreach ($xhprofFiles as $file) {
        echo "  - " . basename($file) . "\n";
    }
}