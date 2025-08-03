<?php

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use BEAR\Resource\ResourceInterface;
use BEAR\Resource\SemanticLog\XhProfile;
use BEAR\Resource\Module\ResourceModule;
use Ray\Di\Injector;

echo "=== AI-Enhanced Debugging Demo ===\n\n";

// Setup
$injector = new Injector(new ResourceModule('FakeVendor\Sandbox'));
$resource = $injector->getInstance(ResourceInterface::class);
$fileManager = new XhProfile(sys_get_temp_dir());

echo " Simulating error scenario for AI debugging...\n\n";

// Simulate multiple requests leading to an error
$scenarios = [
    ['uri' => 'app://self/bird/canary', 'params' => ['id' => 1], 'expected' => 'success'],
    ['uri' => 'app://self/bird/sparrow', 'params' => ['id' => 2], 'expected' => 'success'],
    ['uri' => 'app://self/bird/invalid', 'params' => ['id' => 999], 'expected' => 'error'],
];

$beforeFiles = $fileManager->getRecentFiles();
$executionLog = [];

foreach ($scenarios as $i => $scenario) {
    echo " Step " . ($i + 1) . ": " . $scenario['uri'] . "\n";
    
    try {
        $startTime = microtime(true);
        $response = $resource->get($scenario['uri'], $scenario['params']);
        $endTime = microtime(true);
        
        $executionLog[] = [
            'step' => $i + 1,
            'uri' => $scenario['uri'],
            'params' => $scenario['params'],
            'result' => 'success',
            'code' => $response->code,
            'execution_time' => ($endTime - $startTime) * 1000, // ms
        ];
        
        echo " Success: HTTP " . $response->code . " (" . 
             number_format(($endTime - $startTime) * 1000, 2) . "ms)\n";
             
    } catch (Exception $e) {
        $endTime = microtime(true);
        
        $executionLog[] = [
            'step' => $i + 1,
            'uri' => $scenario['uri'],
            'params' => $scenario['params'],
            'result' => 'error',
            'error' => $e->getMessage(),
            'error_class' => get_class($e),
            'execution_time' => ($endTime - $startTime) * 1000,
        ];
        
        echo " Error: " . $e->getMessage() . " (" . 
             number_format(($endTime - $startTime) * 1000, 2) . "ms)\n";
    }
}

echo " Analyzing generated debugging data...\n";

// Check for new XHProf files
$afterFiles = $fileManager->getRecentFiles(20);
$newFiles = array_diff($afterFiles, $beforeFiles);

echo " XHProf files: " . count($newFiles) . " new profiling files\n";

// Check semantic log files
$logFiles = glob(__DIR__ . '/../log/*.json');
if (!empty($logFiles)) {
    usort($logFiles, function($a, $b) {
        return filemtime($b) <=> filemtime($a);
    });
    
    $recentLogs = array_slice($logFiles, 0, 3);
    echo "   📝 Log files: " . count($recentLogs) . " recent semantic logs\n";
}

echo " Generating AI debugging context...\n";

// Create comprehensive debugging context for AI
$debuggingContext = [
    'scenario' => 'Error investigation with performance profiling',
    'execution_summary' => $executionLog,
    'timeline' => [
        'total_requests' => count($scenarios),
        'successful_requests' => count(array_filter($executionLog, fn($log) => $log['result'] === 'success')),
        'failed_requests' => count(array_filter($executionLog, fn($log) => $log['result'] === 'error')),
        'total_execution_time' => array_sum(array_column($executionLog, 'execution_time')),
    ],
    'available_data' => [
        'semantic_logs' => count($recentLogs ?? []),
        'xhprof_profiles' => count($newFiles),
        'log_files' => array_map('basename', $recentLogs ?? []),
        'xhprof_files' => array_map('basename', $newFiles),
    ],
];

echo " Execution Summary:\n";
echo "      - Total requests: " . $debuggingContext['timeline']['total_requests'] . "\n";
echo "      - Successful: " . $debuggingContext['timeline']['successful_requests'] . "\n";
echo "      - Failed: " . $debuggingContext['timeline']['failed_requests'] . "\n";
echo "      - Total time: " . number_format($debuggingContext['timeline']['total_execution_time'], 2) . "ms\n";

echo " Available debugging data for AI:\n";
echo " Semantic logs: " . $debuggingContext['available_data']['semantic_logs'] . " files\n";
echo " Performance profiles: " . $debuggingContext['available_data']['xhprof_profiles'] . " files\n";

if (!empty($newFiles)) {
    echo " Sample XHProf analysis:\n";
    $sampleFile = $newFiles[0];
    $xhprofData = $fileManager->readData($sampleFile);
    
    if ($xhprofData !== null) {
        $summary = $fileManager->getSummary($xhprofData);
        echo "  Execution time: " . number_format($summary['total_time_microseconds']) . " μs\n";
        echo " Function calls: " . number_format($summary['total_calls']) . "\n";
        echo " Memory usage: " . number_format($summary['total_memory_bytes']) . " bytes\n";
        
        if (!empty($summary['slowest_functions'])) {
            echo " Slowest functions:\n";
            foreach (array_slice($summary['slowest_functions'], 0, 3) as $func) {
                echo "      - " . $func['function'] . ": " . 
                     number_format($func['wall_time']) . " μs (" . $func['calls'] . " calls)\n";
            }
        }
    }
}

echo "\n" . str_repeat('=', 60) . "\n";
echo " AI DEBUGGING WORKFLOW DEMONSTRATION\n";
echo str_repeat('=', 60) . "\n";

echo "\n📤 Before (Traditional debugging):\n";
echo "   1. Error occurs\n";
echo "   2. Developer sees stack trace only\n";
echo "   3. AI guesses from limited context\n";
echo "   4. Multiple trial-and-error iterations\n";

echo "\n📥 After (With semantic logging + XHProf):\n";
echo "   1. Error occurs with rich context\n";
echo "   2. Semantic logs capture request flow\n";
echo "   3. XHProf provides performance bottlenecks\n";
echo "   4. AI gets complete execution picture\n";
echo "   5. More accurate, faster debugging\n";

echo " Data available to AI for debugging:\n";
echo " Request parameters and sequence\n";
echo " Response codes and timing\n";
echo " Function call graphs\n";
echo " Performance bottlenecks\n";
echo " Memory usage patterns\n";
echo " Error context and stack traces\n";

// Save debugging context for easy access
$contextFile = __DIR__ . '/../log/ai_debugging_context.json';
file_put_contents($contextFile, json_encode($debuggingContext, JSON_PRETTY_PRINT));

echo " Complete debugging context saved to: " . basename($contextFile) . "\n";
echo " Next steps:\n";
echo "   1. When errors occur, share semantic log + XHProf files with AI\n";
echo "   2. AI analyzes complete execution context\n";
echo "   3. Get precise debugging suggestions with root cause analysis\n";

echo "\nIntegration complete! Enhanced AI debugging is now active.\n";