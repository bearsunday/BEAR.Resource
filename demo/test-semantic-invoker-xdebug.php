<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BEAR\Resource\Fake\SemanticLogger\Module\TestModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\SemanticLog\Module\SemanticLoggerModule;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

echo "=== Semantic Invoker Xdebug Test ===\n\n";

echo "Xdebug mode: " . (ini_get('xdebug.mode') ?: 'not set') . "\n";

$combinedModule = new class extends AbstractModule {
    protected function configure(): void
    {
        $this->install(new TestModule());
        $this->install(new SemanticLoggerModule());
    }
};

$injector = new Injector($combinedModule);
$resource = $injector->getInstance(ResourceInterface::class);

echo "Making resource request...\n";

// Clear old files
$xdebugDir = __DIR__ . '/../tests/tmp/xdebug';
$logDir = __DIR__ . '/../tests/tmp/SemanticLoggerTest';
@mkdir($xdebugDir, 0755, true);
@mkdir($logDir, 0755, true);

// Clean up old files
array_map('unlink', glob($xdebugDir . '/*.xt'));
array_map('unlink', glob($logDir . '/*.json'));

$result = $resource->get('app://self/simple', ['id' => 'trace-test']);

echo "Response code: " . $result->code . "\n";
echo "Response body: " . json_encode($result->body) . "\n\n";

// Check for xdebug files
echo "=== Checking Xdebug Files ===\n";
$xdebugFiles = glob($xdebugDir . '/*.xt');
if (!empty($xdebugFiles)) {
    foreach ($xdebugFiles as $file) {
        $size = filesize($file);
        echo "✅ Xdebug trace: " . basename($file) . " (size: $size bytes)\n";
        
        if ($size > 0 && $size < 10000) { // Show content if reasonable size
            $content = file_get_contents($file);
            $lines = explode("\n", $content);
            echo "First 5 lines:\n";
            foreach (array_slice($lines, 0, 5) as $line) {
                if (trim($line)) {
                    echo "  " . trim($line) . "\n";
                }
            }
            echo "\n";
        }
    }
} else {
    echo "❌ No Xdebug trace files found\n";
}

// Check for log files
echo "=== Checking Log Files ===\n";
$logFiles = glob($logDir . '/*.json');
if (!empty($logFiles)) {
    foreach ($logFiles as $file) {
        echo "📄 Log file: " . basename($file) . "\n";
        $content = file_get_contents($file);
        $log = json_decode($content, true);
        
        if (isset($log['close']['context']['xdebug_trace_file'])) {
            echo "  Contains xdebug_trace_file: " . basename($log['close']['context']['xdebug_trace_file']) . "\n";
        } else {
            echo "  No xdebug_trace_file in context\n";
        }
        
        if (isset($log['close']['context']['xhprof_file'])) {
            echo "  Contains xhprof_file: " . basename($log['close']['context']['xhprof_file']) . "\n";
        }
        echo "\n";
    }
} else {
    echo "❌ No log files found\n";
}