<?php

declare(strict_types=1);

use BEAR\Resource\SemanticLog\Profile\XdebugTrace;

require_once __DIR__ . '/vendor/autoload.php';

echo "=== Xdebug Trace Debug ===\n";
echo "XDEBUG_MODE: " . (getenv('XDEBUG_MODE') ?: 'not set') . "\n";
echo "xdebug.mode: " . (ini_get('xdebug.mode') ?: 'not set') . "\n";
echo "xdebug_start_trace function: " . (function_exists('xdebug_start_trace') ? 'exists' : 'not exists') . "\n";
echo "xdebug_stop_trace function: " . (function_exists('xdebug_stop_trace') ? 'exists' : 'not exists') . "\n";
echo "xdebug_get_tracefile_name function: " . (function_exists('xdebug_get_tracefile_name') ? 'exists' : 'not exists') . "\n";

echo "\n=== Starting Trace ===\n";
$trace = XdebugTrace::start();
echo "Trace started\n";

// Some test code to trace
function testFunction1(): string
{
    return testFunction2();
}

function testFunction2(): string
{
    return "Hello from trace test!";
}

$result = testFunction1();
echo "Test result: $result\n";

echo "\n=== Stopping Trace ===\n";
$stoppedTrace = $trace->stop();
echo "Trace stopped\n";

echo "\n=== Trace Result ===\n";
$jsonData = $stoppedTrace->jsonSerialize();
echo "JSON data: " . json_encode($jsonData, JSON_PRETTY_PRINT) . "\n";

if (isset($jsonData['file']) && file_exists($jsonData['file'])) {
    echo "\n=== Trace File Content (first 20 lines) ===\n";
    $lines = file($jsonData['file']);
    foreach (array_slice($lines, 0, 20) as $i => $line) {
        echo sprintf("%2d: %s", $i + 1, $line);
    }
    if (count($lines) > 20) {
        echo "... (" . (count($lines) - 20) . " more lines)\n";
    }
} else {
    echo "No trace file generated\n";
}