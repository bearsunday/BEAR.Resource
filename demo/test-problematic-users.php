<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\Module\ResourceModule;
use BEAR\Resource\SemanticLog\Module\DevSemanticLoggerModule;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

// Test module with semantic logging
final class TestProblematicModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->install(new ResourceModule('FakeVendor\Sandbox'));
        $this->override(new DevSemanticLoggerModule('/tmp'));
    }
}

echo "=== Testing Problematic Resource with Profiling ===\n";
echo "This will generate N+1 queries and slow operations.\n\n";

// Create resource client with semantic logging
$injector = new Injector(new TestProblematicModule());
$resource = $injector->getInstance(ResourceInterface::class);

// Execute existing demo resource that already has issues
$result = $resource->get('app://self/bird/canary');

echo "Request completed with status: " . $result->code . "\n";
echo "Check /tmp for semantic log files!\n";

// Find the latest semantic log
$files = glob('/tmp/semantic-dev-*.json');
if ($files) {
    $latest = array_pop($files);
    echo "Latest log file: $latest\n";
}
