<?php

declare(strict_types=1);

/**
 * AI Profile Analysis Demo
 * 
 * This demo creates fake semantic logs with profiling data to demonstrate
 * how AI can analyze performance bottlenecks and code issues.
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

// Create fake semantic log with intentional performance issues
function createFakeSemanticLog(): array
{
    return [
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
        'events' => [
            [
                'id' => 'db_query_1',
                'type' => 'database_query',
                'timestamp' => '2025-01-01T10:00:00.100Z',
                'context' => [
                    'query' => 'SELECT * FROM users WHERE active = 1',
                    'duration_ms' => 15.2,
                    'rows_returned' => 100
                ]
            ],
            [
                'id' => 'db_query_2',
                'type' => 'database_query', 
                'timestamp' => '2025-01-01T10:00:00.120Z',
                'context' => [
                    'query' => 'SELECT * FROM profiles WHERE user_id = 1',
                    'duration_ms' => 8.5,
                    'rows_returned' => 1
                ]
            ],
            [
                'id' => 'db_query_3',
                'type' => 'database_query',
                'timestamp' => '2025-01-01T10:00:00.135Z', 
                'context' => [
                    'query' => 'SELECT * FROM profiles WHERE user_id = 2',
                    'duration_ms' => 8.3,
                    'rows_returned' => 1
                ]
            ],
            // ... repeat for 98 more users (N+1 problem)
            [
                'id' => 'db_query_100',
                'type' => 'database_query',
                'timestamp' => '2025-01-01T10:00:01.200Z',
                'context' => [
                    'query' => 'SELECT * FROM profiles WHERE user_id = 100', 
                    'duration_ms' => 12.1,
                    'rows_returned' => 1
                ]
            ],
            [
                'id' => 'cache_miss_1',
                'type' => 'cache_operation',
                'timestamp' => '2025-01-01T10:00:01.250Z',
                'context' => [
                    'operation' => 'get',
                    'key' => 'user_permissions_1',
                    'result' => 'miss',
                    'duration_ms' => 2.1
                ]
            ],
            [
                'id' => 'external_api_1',
                'type' => 'http_request',
                'timestamp' => '2025-01-01T10:00:01.300Z',
                'context' => [
                    'url' => 'https://api.example.com/permissions/1',
                    'method' => 'GET',
                    'status_code' => 200,
                    'duration_ms' => 450.5
                ]
            ]
        ],
        'close' => [
            'id' => 'bear_resource_complete_1',
            'type' => 'bear_resource_complete',
            '$schema' => 'https://bearsunday.github.io/BEAR.Resource/schemas/complete-context.json',
            'context' => [
                'uri' => 'app://self/user/list?page=1',
                'code' => 200,
                'headers' => [
                    'Content-Type' => 'application/json'
                ],
                'body' => [
                    'users' => '... (100 users with profiles)',
                    'total' => 100,
                    'page' => 1
                ],
                'view' => '{"users":[...],"total":100,"page":1}',
                'xhprofFile' => '/tmp/xhprof_app___self_user_list_page=1_demo123.xhprof',
                'xdebugTraceFile' => '/tmp/xdebug_trace_demo123.xt.gz',
                'total_duration_ms' => 1250.8,
                'memory_peak_mb' => 45.2
            ],
            'openId' => 'bear_resource_request_1'
        ],
        'links' => []
    ];
}

// Generate the fake log
$semanticLog = createFakeSemanticLog();
$jsonOutput = json_encode($semanticLog, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);

// Save to file
$outputFile = __DIR__ . '/fake-semantic-log-with-issues.json';
file_put_contents($outputFile, $jsonOutput);

echo "Fake semantic log created: $outputFile\n\n";
echo "=== AI ANALYSIS PROMPT ===\n\n";

$prompt = <<<'PROMPT'
以下のセマンティックログを分析してください：

**期待する結果 (A)**: ユーザー一覧API (`app://self/user/list`) が200ms以内で応答する
**実際の結果 (B)**: 1250ms かかっている

## 分析してほしい点：

1. **パフォーマンス問題**: なぜ期待する200ms以内ではなく1250msもかかっているのか？
2. **ボトルネック特定**: 最も時間を消費している処理は何か？
3. **重複処理検出**: 同じような処理が繰り返し呼ばれている箇所はあるか？
4. **改善提案**: 具体的にどこをどう修正すれば性能が向上するか？

## セマンティックログ：

```json
[JSON_DATA_HERE]
```

## 回答形式：

### 🔍 **問題分析**
- 期待値200msに対して実際1250ms（6.25倍遅い）の原因

### ⚡ **主要ボトルネック**
- 最も時間を消費している処理（処理時間と割合）

### 🔄 **重複処理・非効率な処理**
- N+1クエリなどの検出結果

### 💡 **改善提案**
- 具体的なコード修正案
- 期待される性能向上効果

PROMPT;

echo $prompt;
echo "\n\n";
echo "=== SAMPLE ANALYSIS (what AI might respond) ===\n\n";

$sampleAnalysis = <<<'ANALYSIS'
### 🔍 **問題分析**
期待値200msに対して実際1250ms（6.25倍遅い）の主な原因：
- データベースクエリが100回実行されている（1回の初期クエリ + 99回の個別クエリ）
- 外部API呼び出しが450ms消費（全体の36%）
- キャッシュミスが発生している

### ⚡ **主要ボトルネック**
1. **データベースクエリ群**: ~800ms (64%)
   - 初期クエリ: 15.2ms
   - N+1クエリ: 99回 × 平均8.5ms = 841.5ms
2. **外部API呼び出し**: 450.5ms (36%)
   - `https://api.example.com/permissions/1` への単一リクエスト

### 🔄 **重複処理・非効率な処理**
- **N+1クエリ問題**: `SELECT * FROM profiles WHERE user_id = ?` が99回実行
- **キャッシュ未活用**: `user_permissions_1` のキャッシュミス後、外部API呼び出し
- **同期処理**: 外部API呼び出しが同期的に実行されている

### 💡 **改善提案**
1. **EagerLoading実装** (推定効果: -800ms)
   ```sql
   -- 修正前: N+1クエリ
   SELECT * FROM users WHERE active = 1;
   SELECT * FROM profiles WHERE user_id = 1; -- ×99回
   
   -- 修正後: JOINクエリ
   SELECT u.*, p.* FROM users u 
   LEFT JOIN profiles p ON u.id = p.user_id 
   WHERE u.active = 1;
   ```

2. **キャッシュ戦略改善** (推定効果: -400ms)
   ```php
   // 権限情報を事前にキャッシュまたはバッチ取得
   $permissions = $cache->getMultiple($userIds);
   ```

3. **非同期処理導入** (推定効果: -200ms)
   ```php
   // 外部API呼び出しを非同期化または遅延実行
   $promise = $httpClient->getAsync('...');
   ```

**期待される総合効果**: 1250ms → 50ms (96%改善)
ANALYSIS;

echo $sampleAnalysis;
echo "\n\n=== Instructions ===\n";
echo "1. Copy the JSON from: $outputFile\n";
echo "2. Replace [JSON_DATA_HERE] in the prompt with the actual JSON\n";
echo "3. Send to your favorite AI (ChatGPT, Claude, etc.)\n";
echo "4. Compare the AI's analysis with the sample above!\n";