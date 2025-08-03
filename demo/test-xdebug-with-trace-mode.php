<?php

declare(strict_types=1);

echo "=== Xdebug Trace Mode Test ===\n\n";

// Check current Xdebug configuration
echo "Xdebug Configuration:\n";
if (extension_loaded('xdebug')) {
    echo "- Loaded: Yes\n";
    echo "- Version: " . phpversion('xdebug') . "\n";
    
    if (function_exists('xdebug_info')) {
        echo "- Mode: " . (ini_get('xdebug.mode') ?: 'not set') . "\n";
    }
    
    echo "- Start trace function: " . (function_exists('xdebug_start_trace') ? 'Available' : 'Not available') . "\n";
    echo "- Stop trace function: " . (function_exists('xdebug_stop_trace') ? 'Available' : 'Not available') . "\n";
} else {
    echo "- Loaded: No\n";
    exit(1);
}

echo "\n=== Manual Trace Test ===\n";

$traceDir = __DIR__ . '/../tests/tmp/xdebug';
@mkdir($traceDir, 0755, true);

$traceFile = $traceDir . '/manual_test_' . uniqid();
echo "Attempting to trace to: $traceFile\n";

try {
    echo "Starting trace...\n";
    xdebug_start_trace($traceFile);
    
    // Do some operations to trace
    function testFunction($param) {
        $result = strlen($param);
        $encoded = json_encode(['length' => $result]);
        return $encoded;
    }
    
    $output = testFunction("Hello, Xdebug tracing!");
    echo "Test operation result: $output\n";
    
    echo "Stopping trace...\n";
    xdebug_stop_trace();
    
    // Check if trace file was created
    $expectedFile = $traceFile . '.xt';
    if (file_exists($expectedFile)) {
        $size = filesize($expectedFile);
        echo "✅ Trace file created: " . basename($expectedFile) . " (size: $size bytes)\n";
        
        if ($size > 0) {
            echo "\nFirst 10 lines of trace:\n";
            $content = file_get_contents($expectedFile);
            $lines = explode("\n", $content);
            foreach (array_slice($lines, 0, 10) as $i => $line) {
                if (trim($line)) {
                    echo "  " . ($i + 1) . ": " . trim($line) . "\n";
                }
            }
        }
        
        return $expectedFile;
    } else {
        echo "❌ Trace file not created: $expectedFile\n";
        return null;
    }
    
} catch (Throwable $e) {
    echo "❌ Error during tracing: " . $e->getMessage() . "\n";
    return null;
}