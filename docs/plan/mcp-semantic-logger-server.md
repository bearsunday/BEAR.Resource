ひょう# MCP Semantic Logger Server Implementation Plan

## Executive Summary

セマンティックログをMCPサーバー経由でAIに提供し、エラー修復の効率を向上させるシステムの実装計画。

## Current State Analysis

### Existing Semantic Logging System
- **SemanticInvoker**: リソース呼び出しを監視し構造化ログを出力
- **ContextFactory**: リクエスト、完了、エラーのコンテキストを生成
- **JSON Schema**: ログエントリの構造定義（open-context.json等）  
- **Log Files**: `/log/` ディレクトリに JSON形式で出力

### Log Structure Example
```json
{
  "test": "testInvokeBasicFlow",
  "timestamp": "2025-07-28T16:59:35+00:00", 
  "calls": [
    {
      "method": "open",
      "context": {
        "session_id": "session_xxx",
        "start_time": 1753721975.282451,
        "uri": "app://self/test",
        "method": "get",
        "query": {"id": 1},
        "type": "resource_request"
      }
    },
    {
      "method": "close", 
      "context": {
        "session_id": "session_xxx_success",
        "code": 200,
        "success": true,
        "type": "resource_result"
      }
    }
  ]
}
```

## Implementation Plan

### Phase 1: MCP Server Foundation

#### 1.1 MCP Protocol Implementation
```php
// src/Mcp/McpServer.php
class McpServer
{
    public function handleRequest(array $jsonRpcRequest): array
    public function listResources(): array
    public function listTools(): array
    public function readResource(string $uri): array
}
```

#### 1.2 Log Reader Service
```php
// src/Mcp/LogReaderService.php
class LogReaderService
{
    public function getRecentLogs(int $limit = 50): array
    public function getLogsByTimeRange(DateTime $start, DateTime $end): array
    public function getLogsBySession(string $sessionId): array
    public function searchLogs(array $criteria): array
}
```

### Phase 2: Resource Endpoints

#### 2.1 Log Query Resources
- `mcp://logs/recent` - 最新のログエントリ
- `mcp://logs/errors` - エラーログのみ
- `mcp://logs/session/{sessionId}` - セッション別ログ
- `mcp://logs/timeline/{timestamp}` - 特定時刻周辺のログ

#### 2.2 Search & Filter Tools
- `search_logs` - キーワード検索
- `filter_by_uri` - URI別フィルタ
- `filter_by_method` - HTTPメソッド別フィルタ
- `analyze_error_context` - エラー文脈解析

### Phase 3: AI Integration Enhancements

#### 3.1 Error Context Enhancement
```php
// src/Mcp/ErrorContextEnhancer.php
class ErrorContextEnhancer
{
    public function enrichErrorWithContext(Throwable $error, array $logs): array
    {
        return [
            'error' => $error->getMessage(),
            'stack_trace' => $error->getTraceAsString(),
            'preceding_requests' => $this->getPrecedingRequests($logs),
            'related_queries' => $this->extractDbQueries($logs),
            'request_flow' => $this->buildRequestFlow($logs),
            'performance_metrics' => $this->extractMetrics($logs)
        ];
    }
}
```

#### 3.2 Debug Assistant Tools
- `get_error_timeline` - エラー発生前後のタイムライン
- `analyze_request_flow` - リクエストフローの分析
- `suggest_debug_points` - デバッグポイントの提案
- `find_similar_errors` - 類似エラーの検索

### Phase 4: Integration Points

#### 4.1 BEAR.Resource Integration
```php
// src/Module/McpServerModule.php
class McpServerModule extends AbstractModule
{
    protected function configure(): void
    {
        $this->bind(McpServerInterface::class)->to(McpServer::class);
        $this->bind(LogReaderInterface::class)->to(LogReaderService::class);
        $this->install(new SemanticLoggerModule());
    }
}
```

#### 4.2 Startup Script
```php
// bin/mcp-server.php
#!/usr/bin/env php
<?php
require_once __DIR__ . '/../vendor/autoload.php';

$injector = new Injector(new McpServerModule());
$server = $injector->getInstance(McpServerInterface::class);
$server->run();
```

## Technical Specifications

### Data Flow
1. **Log Generation**: SemanticInvoker → JSON files
2. **Log Reading**: McpServer → LogReaderService → JSON parsing
3. **AI Query**: Claude Code → MCP Protocol → Log data
4. **Context Enhancement**: Raw logs → Enriched error context

### Performance Considerations
- **Log Rotation**: 日次ローテーション、1週間保持
- **Indexing**: タイムスタンプ・セッションID・URIでインデックス
- **Caching**: 最新ログのメモリキャッシュ
- **Streaming**: 大量ログの場合はストリーミング対応

### Security
- **Access Control**: ローカルホストからのアクセスのみ
- **Log Sanitization**: 機密情報のマスキング
- **Rate Limiting**: クエリレート制限

## Expected Benefits

### For AI Debugging
1. **Contextual Awareness**: エラー発生前の処理フローを把握
2. **Root Cause Analysis**: 関連するリクエスト・クエリから原因特定
3. **Pattern Recognition**: 類似エラーのパターン発見
4. **Performance Insights**: ボトルネック箇所の特定

### For Developers
1. **Automated Debugging**: AIによる自動デバッグ支援
2. **Comprehensive Logging**: 構造化された実行ログ
3. **Historical Analysis**: 過去のエラーパターン分析
4. **Integration Testing**: ログベースのテスト検証

## Implementation Timeline

- **Week 1**: MCP Protocol & Log Reader Service
- **Week 2**: Resource Endpoints & Search Tools  
- **Week 3**: Error Context Enhancement & Debug Tools
- **Week 4**: Integration & Testing

## Dependencies

- **PHP 8.1+**: 既存のBEAR.Resourceと同じ
- **JSON-RPC**: MCP通信プロトコル
- **File System**: ログファイルアクセス
- **Optional**: SQLite for log indexing

## Success Metrics

1. **Response Time**: ログクエリ < 100ms
2. **Coverage**: エラー文脈情報 90%以上
3. **Accuracy**: AI修復成功率 70%向上
4. **Usability**: 開発者の設定作業 < 5分

## Next Steps

1. MCP Protocol仕様の詳細確認
2. LogReaderServiceのプロトタイプ実装
3. Claude Code連携テスト
4. パフォーマンス最適化
