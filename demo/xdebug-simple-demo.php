<?php

declare(strict_types=1);

echo "=== Simple Xdebug Demo ===\n";

echo "Xdebug loaded: " . (extension_loaded('xdebug') ? 'Yes' : 'No') . "\n";
if (extension_loaded('xdebug')) {
    echo "Xdebug version: " . phpversion('xdebug') . "\n";
}

// Try basic xdebug functions
if (function_exists('xdebug_start_trace')) {
    echo "xdebug_start_trace function exists\n";
} else {
    echo "xdebug_start_trace function NOT available\n";
}

if (function_exists('xdebug_stop_trace')) {
    echo "xdebug_stop_trace function exists\n";
} else {
    echo "xdebug_stop_trace function NOT available\n";
}

// Try to start tracing
$traceFile = __DIR__ . '/../tests/tmp/xdebug/simple_trace_test';
@mkdir(dirname($traceFile), 0755, true);

echo "\nAttempting to start trace to: $traceFile\n";

try {
    if (function_exists('xdebug_start_trace')) {
        xdebug_start_trace($traceFile);
        echo "Trace started successfully\n";
        
        // Do some simple operations
        $result = 1 + 1;
        $array = ['test' => 'value'];
        $json = json_encode($array);
        
        xdebug_stop_trace();
        echo "Trace stopped successfully\n";
        
        // Check if trace file was created
        $expectedFile = $traceFile . '.xt';
        if (file_exists($expectedFile)) {
            $size = filesize($expectedFile);
            echo "Trace file created: $expectedFile (size: $size bytes)\n";
            
            // Show first few lines
            $content = file_get_contents($expectedFile);
            $lines = explode("\n", $content);
            echo "First 5 lines of trace:\n";
            foreach (array_slice($lines, 0, 5) as $line) {
                echo "  $line\n";
            }
        } else {
            echo "Trace file not created: $expectedFile\n";
        }
    } else {
        echo "xdebug_start_trace not available\n";
    }
} catch (Throwable $e) {
    echo "Error during tracing: " . $e->getMessage() . "\n";
}