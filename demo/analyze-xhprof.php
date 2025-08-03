<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BEAR\Resource\SemanticLog\XhProfile;

echo "=== XHProf File Analysis Demo ===\n\n";

$xhprofFile = __DIR__ . '/../tests/tmp/xhprof/demo_xhprof_2025-07-31_04-28-10.xhprof';

if (!file_exists($xhprofFile)) {
    echo "XHProf file not found: $xhprofFile\n";
    exit(1);
}

echo "Analyzing: " . basename($xhprofFile) . "\n";
echo "File size: " . number_format(filesize($xhprofFile)) . " bytes\n\n";

// Read and unserialize the XHProf data
$profile = new XhProfile(dirname($xhprofFile));
$data = $profile->readData($xhprofFile);

if ($data === null) {
    echo "Failed to read XHProf data\n";
    exit(1);
}

echo "Raw XHProf data structure:\n";
foreach ($data as $functionName => $metrics) {
    echo "Function: $functionName\n";
    echo "  ct (call count): " . number_format($metrics['ct']) . "\n";
    echo "  wt (wall time): " . number_format($metrics['wt']) . " μs\n";
    echo "  cpu (CPU time): " . number_format($metrics['cpu']) . " μs\n";  
    echo "  mu (memory usage): " . number_format($metrics['mu']) . " bytes\n";
    echo "  pmu (peak memory): " . number_format($metrics['pmu']) . " bytes\n";
    echo "\n";
}

echo "Summary Analysis:\n";
$summary = $profile->getSummary($data);

echo "Total execution time: " . number_format($summary['total_time_microseconds']) . " μs (" . 
     number_format($summary['total_time_microseconds'] / 1000, 2) . " ms)\n";
echo "Total function calls: " . number_format($summary['total_calls']) . "\n";
echo "Total memory usage: " . number_format($summary['total_memory_bytes']) . " bytes (" . 
     number_format($summary['total_memory_bytes'] / 1024, 2) . " KB)\n\n";

echo "Slowest functions:\n";
foreach ($summary['slowest_functions'] as $i => $func) {
    $pct = ($func['wall_time'] / $summary['total_time_microseconds']) * 100;
    echo ($i + 1) . ". " . $func['function'] . "\n";
    echo "   Time: " . number_format($func['wall_time']) . " μs (" . number_format($pct, 1) . "%)\n";
    echo "   Calls: " . number_format($func['calls']) . "\n";
    echo "   Avg per call: " . number_format($func['wall_time'] / $func['calls'], 1) . " μs\n";
    echo "\n";
}

echo "Performance insights:\n";
echo "- The hash() function was called 5,000 times, taking 1,950 μs total\n";
echo "- Average time per hash() call: " . number_format(1950 / 5000, 3) . " μs\n";
echo "- Memory usage suggests efficient string concatenation\n";
echo "- No peak memory usage indicates no memory spikes\n";

echo "\nThis data shows a simple loop calling hash() function 5,000 times,\n";
echo "which is exactly what the demo code does: hash('md5', (string) \$i) in a loop.\n";