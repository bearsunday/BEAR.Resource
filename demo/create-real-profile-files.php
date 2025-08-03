<?php

declare(strict_types=1);

/**
 * Create Real Profile Files for AI Testing
 * 
 * This script creates actual XHProf and Xdebug trace files
 * that AI can analyze to test its profiling capabilities.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

echo "Creating real profile files for AI testing...\n\n";

// 1. Create a realistic XHProf file
function createXhprofFile(): string
{
    $xhprofData = [
        'main()' => [
            'ct' => 1,
            'wt' => 1250834, // 1250ms in microseconds
            'cpu' => 1200000,
            'mu' => 47185920, // ~45MB
            'pmu' => 47185920
        ],
        'main()==>BEAR\\Resource\\Resource::get' => [
            'ct' => 1,
            'wt' => 1230000,
            'cpu' => 1180000,
            'mu' => 1024000,
            'pmu' => 1024000
        ],
        'main()==>BEAR\\Resource\\Resource::get==>UserListResource::onGet' => [
            'ct' => 1,
            'wt' => 1200000,
            'cpu' => 1150000,
            'mu' => 2048000,
            'pmu' => 2048000
        ],
        'main()==>BEAR\\Resource\\Resource::get==>UserListResource::onGet==>PDO::query' => [
            'ct' => 100, // 100 queries! N+1 problem
            'wt' => 850000, // 850ms total
            'cpu' => 800000,
            'mu' => 5120000,
            'pmu' => 5120000
        ],
        'main()==>BEAR\\Resource\\Resource::get==>UserListResource::onGet==>GuzzleHttp\\Client::get' => [
            'ct' => 1,
            'wt' => 450500, // 450ms external API
            'cpu' => 10000,
            'mu' => 1024000,
            'pmu' => 1024000
        ],
        'main()==>BEAR\\Resource\\Resource::get==>UserListResource::onGet==>UserProfile::load' => [
            'ct' => 100, // Called 100 times
            'wt' => 800000,
            'cpu' => 750000,
            'mu' => 10240000,
            'pmu' => 10240000
        ]
    ];
    
    $filename = sys_get_temp_dir() . '/xhprof_ai_test_' . uniqid() . '.xhprof';
    file_put_contents($filename, serialize($xhprofData));
    
    return $filename;
}

// 2. Create a realistic Xdebug trace file (simplified format)
function createXdebugTraceFile(): string
{
    $traceContent = <<<TRACE
Version: 3.1.1
File format: 4
TRACE START [2025-01-01 10:00:00.000000]
           0.0000     654368   -> {main}() /app/user/list.php:0
           0.0001     654400     -> BEAR\Resource\Resource->get() /app/user/list.php:15
           0.0002     654500       -> UserListResource->onGet() /vendor/bear/resource/src/Resource.php:45
           0.0003     654600         -> PDO->query() /app/UserListResource.php:25
                                        >=> 'SELECT * FROM users WHERE active = 1'
           0.0152     756800         <- PDO->query() /app/UserListResource.php:25
           0.0153     756900         -> UserProfile->load() /app/UserListResource.php:30
           0.0154     757000           -> PDO->query() /app/UserProfile.php:15
                                          >=> 'SELECT * FROM profiles WHERE user_id = 1'
           0.0238     859200           <- PDO->query() /app/UserProfile.php:15
           0.0239     859300         <- UserProfile->load() /app/UserListResource.php:30
           0.0240     859400         -> UserProfile->load() /app/UserListResource.php:30
           0.0241     859500           -> PDO->query() /app/UserProfile.php:15
                                          >=> 'SELECT * FROM profiles WHERE user_id = 2'
           0.0324     961700           <- PDO->query() /app/UserProfile.php:15
           0.0325     961800         <- UserProfile->load() /app/UserListResource.php:30
           ... [98 more similar UserProfile->load() calls] ...
           1.2000    4567890         -> GuzzleHttp\Client->get() /app/UserListResource.php:45
                                        >=> 'https://api.example.com/permissions/1'
           1.6505    5567890         <- GuzzleHttp\Client->get() /app/UserListResource.php:45
           1.2508    5567990       <- UserListResource->onGet() /vendor/bear/resource/src/Resource.php:45
           1.2509    5568000     <- BEAR\Resource\Resource->get() /app/user/list.php:15
           1.2510    5568100   <- {main}() /app/user/list.php:0
TRACE END   [2025-01-01 10:00:01.251000]
TRACE;

    $filename = sys_get_temp_dir() . '/xdebug_ai_test_' . uniqid() . '.xt';
    file_put_contents($filename, $traceContent);
    
    return $filename;
}

// Create the files
$xhprofFile = createXhprofFile();
$xdebugFile = createXdebugTraceFile();

echo "✅ XHProf file created: $xhprofFile\n";
echo "✅ Xdebug trace file created: $xdebugFile\n\n";

// Update semantic log with real file paths
$semanticLog = [
    'schemaUrl' => 'https://koriym.github.io/Koriym.SemanticLogger/schemas/semantic-log.json',
    'open' => [
        'id' => 'bear_resource_request_1',
        'type' => 'bear_resource_request',
        '$schema' => 'https://bearsunday.github.io/BEAR.Resource/schemas/open-context.json',
        'context' => [
            'method' => 'GET',
            'uri' => 'app://self/user/list?page=1'
        ]
    ],
    'close' => [
        'id' => 'bear_resource_complete_1',
        'type' => 'bear_resource_complete',
        '$schema' => 'https://bearsunday.github.io/BEAR.Resource/schemas/complete-context.json',
        'context' => [
            'uri' => 'app://self/user/list?page=1',
            'code' => 200,
            'headers' => ['Content-Type' => 'application/json'],
            'body' => ['users' => '... (100 users)', 'total' => 100],
            'view' => '{"users":[...],"total":100}',
            'xhprofFile' => $xhprofFile,
            'xdebugTraceFile' => $xdebugFile,
            'total_duration_ms' => 1250.8,
            'memory_peak_mb' => 45.2
        ],
        'openId' => 'bear_resource_request_1'
    ],
    'events' => [],
    'links' => []
];

$logFile = __DIR__ . '/semantic-log-with-real-files.json';
file_put_contents($logFile, json_encode($semanticLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

echo "✅ Updated semantic log: $logFile\n\n";

echo "=== AI TEST PROMPT ===\n\n";
echo "以下のセマンティックログにはXHProfとXdebugの実際のプロファイルファイルが含まれています。\n";
echo "これらのファイルを解析して、パフォーマンス問題を特定してください：\n\n";

echo "セマンティックログ:\n";
echo file_get_contents($logFile);
echo "\n\n";

echo "XHProfファイルの内容:\n";
echo "```\n";
echo file_get_contents($xhprofFile);
echo "\n```\n\n";

echo "Xdebugトレースファイルの内容:\n";
echo "```\n";
echo file_get_contents($xdebugFile);
echo "\n```\n\n";

echo "質問: これらのプロファイルデータから、具体的にどのような問題が見つかりますか？\n";
echo "改善提案も含めて分析してください。\n";