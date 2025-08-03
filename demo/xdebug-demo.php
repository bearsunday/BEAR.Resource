<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BEAR\Resource\Fake\SemanticLogger\Module\TestModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\SemanticLog\Module\SemanticLoggerModule;
use Override;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

echo "=== Xdebug Integration Demo ===\n\n";

$combinedModule = new class extends AbstractModule {
    #[Override]
    protected function configure(): void
    {
        $this->install(new TestModule());
        $this->install(new SemanticLoggerModule());
    }
};

$injector = new Injector($combinedModule);
$resource = $injector->getInstance(ResourceInterface::class);

echo "Testing resource with Xdebug integration...\n";

// Make a simple request
$result = $resource->get('app://self/simple', ['id' => 'demo-test']);

echo "Response code: " . $result->code . "\n";
echo "Response body: " . json_encode($result->body) . "\n\n";

// Check for generated log files
$tmpDir = __DIR__ . '/../tests/tmp/SemanticLoggerTest';
$xdebugDir = __DIR__ . '/../tests/tmp/xdebug';

echo "Checking for log files in: $tmpDir\n";
$logFiles = glob($tmpDir . '/*.json');
if (!empty($logFiles)) {
    $latestLog = end($logFiles);
    echo "Latest log file: " . basename($latestLog) . "\n";
    
    $content = file_get_contents($latestLog);
    $log = json_decode($content, true);
    
    if (isset($log['close']['context']['xdebug_trace_file'])) {
        echo "Xdebug trace file: " . $log['close']['context']['xdebug_trace_file'] . "\n";
        
        if (file_exists($log['close']['context']['xdebug_trace_file'])) {
            $fileSize = filesize($log['close']['context']['xdebug_trace_file']);
            echo "Trace file size: " . number_format($fileSize) . " bytes\n";
        } else {
            echo "Trace file not found on disk\n";
        }
    } else {
        echo "No xdebug_trace_file in log context\n";
    }
    
    if (isset($log['close']['context']['xhprof_file'])) {
        echo "XHProf file: " . $log['close']['context']['xhprof_file'] . "\n";
    }
} else {
    echo "No log files found\n";
}

echo "\nChecking for Xdebug trace files in: $xdebugDir\n";
$traceFiles = glob($xdebugDir . '/*.xt');
if (!empty($traceFiles)) {
    foreach ($traceFiles as $traceFile) {
        echo "Trace file: " . basename($traceFile);
        echo " (size: " . number_format(filesize($traceFile)) . " bytes)\n";
    }
} else {
    echo "No trace files found\n";
}

echo "\nXdebug status: " . (extension_loaded('xdebug') ? 'loaded' : 'not loaded') . "\n";
if (extension_loaded('xdebug')) {
    echo "Xdebug version: " . phpversion('xdebug') . "\n";
}