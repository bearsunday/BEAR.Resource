<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BEAR\Resource\Fake\SemanticLogger\Module\TestModule;
use BEAR\Resource\Invoker;
use BEAR\Resource\InvokerInterface;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\SemanticLog\ContextFactoryInterface;
use BEAR\Resource\SemanticLog\Module\SemanticLoggerModule;
use BEAR\Resource\SemanticLog\SemanticInvoker;
use Koriym\SemanticLogger\SemanticLoggerInterface;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

echo "=== Force Xdebug Test ===\n\n";

// Create a module that forces SemanticInvoker to be used
$forceSemanticModule = new class extends AbstractModule {
    protected function configure(): void
    {
        $this->install(new TestModule());
        $this->install(new SemanticLoggerModule());
        
        // Force override with explicit binding
        $this->bind(InvokerInterface::class)->to(SemanticInvoker::class);
    }
};

$injector = new Injector($forceSemanticModule);

// Verify the binding
$invoker = $injector->getInstance(InvokerInterface::class);
echo "Invoker class: " . get_class($invoker) . "\n";

if ($invoker instanceof SemanticInvoker) {
    echo "✅ SemanticInvoker is bound correctly\n";
} else {
    echo "❌ SemanticInvoker is NOT bound\n";
    exit(1);
}

// Test with clean directories
$xdebugDir = __DIR__ . '/../tests/tmp/xdebug';
$xhprofDir = __DIR__ . '/../tests/tmp/xhprof';
$logDir = __DIR__ . '/../tests/tmp/SemanticLoggerTest';

@mkdir($xdebugDir, 0755, true);
@mkdir($xhprofDir, 0755, true);
@mkdir($logDir, 0755, true);

// Clean old files
foreach (glob($xdebugDir . '/*') as $file) { unlink($file); }
foreach (glob($xhprofDir . '/*') as $file) { unlink($file); }
foreach (glob($logDir . '/*') as $file) { unlink($file); }

echo "\nCleaned all directories\n";

$resource = $injector->getInstance(ResourceInterface::class);

echo "Making resource request...\n";
$result = $resource->get('app://self/simple', ['id' => 'force-test']);

echo "Response code: " . $result->code . "\n";
echo "Response ID: " . $result->body['id'] . "\n";

echo "\n=== File Check ===\n";

// Check Xdebug files
$xdebugFiles = glob($xdebugDir . '/*');
echo "Xdebug files created: " . count($xdebugFiles) . "\n";
foreach ($xdebugFiles as $file) {
    echo "  - " . basename($file) . " (" . filesize($file) . " bytes)\n";
    if (filesize($file) > 0) {
        $content = file_get_contents($file);
        $lines = explode("\n", $content);
        echo "    First line: " . trim($lines[0]) . "\n";
    }
}

// Check XHProf files  
$xhprofFiles = glob($xhprofDir . '/*');
echo "XHProf files created: " . count($xhprofFiles) . "\n";
foreach ($xhprofFiles as $file) {
    echo "  - " . basename($file) . " (" . filesize($file) . " bytes)\n";
}

// Check log files
$logFiles = glob($logDir . '/*');
echo "Log files created: " . count($logFiles) . "\n";
foreach ($logFiles as $file) {
    echo "  - " . basename($file) . " (" . filesize($file) . " bytes)\n";
    $content = file_get_contents($file);
    $log = json_decode($content, true);
    if (isset($log['close']['context']['xdebug_trace_file'])) {
        echo "    Contains xdebug_trace_file: Yes\n";
    }
    if (isset($log['close']['context']['xhprof_file'])) {
        echo "    Contains xhprof_file: Yes\n";
    }
}