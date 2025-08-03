<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\SemanticLog\XhProfile;
use BEAR\Resource\Module\ResourceModule;
use Ray\Di\Injector;

echo "=== XHProf Integration Demo ===\n\n";

// Check if XHProf is available
if (! extension_loaded('xhprof')) {
    echo "  XHProf extension is not loaded.\n";
    echo "   Install XHProf to see profiling data in semantic logs.\n";
    echo "   The integration will work silently without XHProf.\n\n";
}

// Setup
$injector = new Injector(new ResourceModule('FakeVendor\Sandbox'));
$resource = $injector->getInstance(ResourceInterface::class);
$fileManager = new XhProfile(sys_get_temp_dir());

echo "🧹 Cleaning up old XHProf files...\n";
$cleaned = $fileManager->cleanup();
echo "   Cleaned up $cleaned old files.\n\n";

echo " Getting recent XHProf files before test...\n";
$beforeFiles = $fileManager->getRecentFiles();
echo "   Found " . count($beforeFiles) . " recent files.\n\n";

echo " Making resource request with semantic logging...\n";
try {
    $response = $resource->get('app://self/bird/canary', ['id' => 1]);
    echo " Request successful: " . $response->code . "\n";
} catch (Exception $e) {
    echo " Request failed: " . $e->getMessage() . "\n";
}

echo " Checking for new XHProf files...\n";
$afterFiles = $fileManager->getRecentFiles();
$newFiles = array_diff($afterFiles, $beforeFiles);

if (empty($newFiles)) {
    echo "  No new XHProf files created (XHProf not available).\n";
} else {
    echo " Created " . count($newFiles) . " new XHProf file(s):\n";
    
    foreach ($newFiles as $file) {
        echo "      - " . basename($file) . "\n";
        
        // Try to read and analyze
        $xhprofData = $fileManager->readData($file);
        if ($xhprofData !== null) {
            $summary = $fileManager->getSummary($xhprofData);
            echo " Total time: " . number_format($summary['total_time_microseconds']) . " μs\n";
            echo " Total calls: " . number_format($summary['total_calls']) . "\n";
            echo " Total memory: " . number_format($summary['total_memory_bytes']) . " bytes\n";
            
            if (!empty($summary['slowest_functions'])) {
                echo " Slowest function: " . $summary['slowest_functions'][0]['function'] . 
                     " (" . number_format($summary['slowest_functions'][0]['wall_time']) . " μs)\n";
            }
        }
    }
}

echo "\n📝 Checking semantic log files...\n";
$logFiles = glob(__DIR__ . '/../log/*.json');
if (!empty($logFiles)) {
    // Get the most recent log file
    usort($logFiles, function($a, $b) {
        return filemtime($b) <=> filemtime($a);
    });
    
    $latestLog = file_get_contents($logFiles[0]);
    $logData = json_decode($latestLog, true);
    
    echo "   📄 Latest log file: " . basename($logFiles[0]) . "\n";
    
    if (isset($logData['calls'])) {
        foreach ($logData['calls'] as $call) {
            if (isset($call['context']['xhprof_file'])) {
                echo "   🔗 XHProf file referenced: " . basename($call['context']['xhprof_file']) . "\n";
                break;
            }
        }
    }
}

echo " Demo completed!\n";
echo "   Now when errors occur, semantic logs will include XHProf profiling data.\n";
echo "   This gives AI debugging tools complete context about performance bottlenecks.\n";