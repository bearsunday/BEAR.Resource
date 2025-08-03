<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BEAR\Resource\Fake\SemanticLogger\Module\TestModule;
use BEAR\Resource\ResourceInterface;
use BEAR\Resource\SemanticLog\Module\SemanticLoggerModule;
use BEAR\Resource\SemanticLog\XdebugTrace;
use Ray\Di\AbstractModule;
use Ray\Di\Injector;

echo "=== Xdebug Integration Demo ===\n\n";

// Show environment status
echo "Environment Status:\n";
echo "- PHP Version: " . PHP_VERSION . "\n";
echo "- Xdebug loaded: " . (extension_loaded('xdebug') ? 'Yes' : 'No') . "\n";
if (extension_loaded('xdebug')) {
    echo "- Xdebug version: " . phpversion('xdebug') . "\n";
    echo "- xdebug_start_trace available: " . (function_exists('xdebug_start_trace') ? 'Yes' : 'No') . "\n";
}

// Demonstrate XdebugTrace utility class
echo "\n=== XdebugTrace Utility Demo ===\n";
$xdebugDir = __DIR__ . '/../tests/tmp/xdebug';
@mkdir($xdebugDir, 0755, true);

$xdebugTrace = new XdebugTrace($xdebugDir);

// Create a mock trace file to demonstrate functionality
$mockTraceFile = $xdebugDir . '/demo_trace_' . uniqid() . '.xt';
$mockTraceContent = "Version: 3.1.2\n" .
"File format: 4\n" .
"TRACE START [2025-07-31 04:45:00.000000]\n" .
"    0.0001     640000   0  -> BEAR\\Resource\\ResourceObject->onGet() /path/to/Simple.php:15\n" .
"    0.0002     642000   1    -> json_encode() /path/to/Simple.php:20\n" .
"    0.0003     642000   1    <- json_encode() /path/to/Simple.php:20\n" .
"    0.0004     644000   1    -> time() /path/to/Simple.php:21\n" .
"    0.0005     644000   1    <- time() /path/to/Simple.php:21\n" .
"    0.0006     644000   0  <- BEAR\\Resource\\ResourceObject->onGet() /path/to/Simple.php:15\n" .
"TRACE END   [2025-07-31 04:45:00.006000]\n";

file_put_contents($mockTraceFile, $mockTraceContent);

echo "Created mock trace file: " . basename($mockTraceFile) . "\n";

// Test trace reading
$content = $xdebugTrace->readTrace($mockTraceFile);
echo "Trace content length: " . strlen($content) . " bytes\n";

// Test summary generation
$summary = $xdebugTrace->getSummary($mockTraceFile);
if ($summary !== null) {
    echo "\nTrace Summary:\n";
    echo "- Total lines: " . $summary['total_lines'] . "\n";
    echo "- Function calls: " . $summary['function_calls'] . "\n";
    echo "- Max call depth: " . $summary['max_call_depth'] . "\n";
    echo "- File size: " . number_format($summary['file_size_bytes']) . " bytes\n";
    
    echo "\nSample lines from trace:\n";
    foreach ($summary['sample_lines'] as $i => $line) {
        if (!empty(trim($line))) {
            echo "  " . ($i + 1) . ": " . trim($line) . "\n";
        }
    }
}

echo "\n=== Integration with Semantic Logging ===\n";

// Show how the integration would work in practice
$mockContext = [
    'uri' => 'app://self/simple?id=demo',
    'method' => 'GET',
    'code' => 200,
    'headers' => ['Content-Type' => 'application/json'],
    'body' => ['id' => 'demo', 'message' => 'Hello from Simple'],
    'view' => '{"id":"demo","message":"Hello from Simple"}',
    'xhprof_file' => '/path/to/xhprof_demo.xhprof',
    'xdebug_trace_file' => $mockTraceFile,
];

echo "Sample semantic log context with both XHProf and Xdebug:\n";
echo json_encode($mockContext, JSON_PRETTY_PRINT) . "\n";

echo "\n=== Environment Recommendations ===\n";
echo "For full Xdebug trace functionality, add to php.ini:\n";
echo "  xdebug.mode=trace\n";
echo "  xdebug.trace_enable_trigger=1\n";
echo "  xdebug.trace_output_dir=/tmp/xdebug\n";
echo "\nOr run with: php -dxdebug.mode=trace script.php\n";

// Cleanup
unlink($mockTraceFile);

echo "\nDemo completed successfully!\n";