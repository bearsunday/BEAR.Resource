# プロファイリングスコープの包括的分析

## 実際の呼び出し層の多様性

### 現実的なプロファイリング対象
```
Application Layer
├── ResourceObject::onGet()      ←── BEAR.Resource固有
├── ServiceClass::businessLogic() ←── アプリケーション層
└── UserRepository::findById()   ←── インフラ層

Infrastructure Layer  
├── PDO::query()                 ←── DB処理
├── Guzzle::request()           ←── HTTP Client
├── Redis::get()                ←── キャッシュ
└── FileSystem::write()         ←── ファイルI/O
```

### 問題: 層を跨ぐプロファイリング需要

**DBアクセス例**:
```php
class UserRepository {
    public function findById(int $id): User {
        // ここでもプロファイリングしたい
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return User::fromArray($stmt->fetch());
    }
}
```

**外部API例**:
```php
class ExternalApiClient {
    public function fetchData(string $endpoint): array {
        // ここでもプロファイリングしたい
        return $this->httpClient->get($endpoint)->toArray();
    }
}
```

## 現在の設計の限界

### Context レベル配置の問題
```php
// CompleteContext は BEAR.Resource の成功時のみ
// ErrorContext は BEAR.Resource の失敗時のみ
// → DB、HTTP、ファイルI/O は対象外
```

### Logger レベル配置の問題
```php
// SemanticLogger も BEAR.Resource 固有のログ
// → 汎用的なプロファイリングには不適切
```

## 解決案: **グローバルプロファイリングマネージャー**

### 設計原則
1. **レイヤー非依存**: どの層からでもプロファイリング可能
2. **セッション管理**: リクエスト全体のプロファイリング統合
3. **選択的記録**: 必要な部分のみプロファイリング

### アーキテクチャ設計

```php
interface ProfilingManagerInterface
{
    public function startSession(string $sessionId): void;
    public function startOperation(string $operationId, string $type): void;
    public function endOperation(string $operationId): void;
    public function endSession(string $sessionId): ProfilingSession;
}

final class ProfilingSession
{
    public function __construct(
        public readonly string $sessionId,
        public readonly array $operations,
        public readonly ?string $xhprofFile,
        public readonly ?string $xdebugTraceFile,
        public readonly float $totalDuration,
    ) {}
}

final class ProfilingOperation
{
    public function __construct(
        public readonly string $id,
        public readonly string $type, // 'resource', 'db', 'http', 'cache'
        public readonly float $startTime,
        public readonly float $endTime,
        public readonly array $metadata,
    ) {}
}
```

### 実装例

#### 1. グローバルプロファイリングマネージャー
```php
final class GlobalProfilingManager implements ProfilingManagerInterface
{
    private array $sessions = [];
    private ?string $currentSessionId = null;
    
    public function startSession(string $sessionId): void
    {
        $this->currentSessionId = $sessionId;
        $this->sessions[$sessionId] = [
            'operations' => [],
            'startTime' => microtime(true),
        ];
        
        // XHProf開始
        if (function_exists('xhprof_enable')) {
            xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        }
        
        // Xdebug開始
        if (function_exists('xdebug_start_trace')) {
            xdebug_start_trace(sys_get_temp_dir() . "/trace_{$sessionId}.xt");
        }
    }
    
    public function startOperation(string $operationId, string $type): void
    {
        if ($this->currentSessionId === null) {
            return; // セッション外では何もしない
        }
        
        $this->sessions[$this->currentSessionId]['operations'][$operationId] = [
            'type' => $type,
            'startTime' => microtime(true),
            'metadata' => [],
        ];
    }
    
    public function endOperation(string $operationId): void
    {
        if ($this->currentSessionId === null) {
            return;
        }
        
        $operation = &$this->sessions[$this->currentSessionId]['operations'][$operationId];
        $operation['endTime'] = microtime(true);
        $operation['duration'] = $operation['endTime'] - $operation['startTime'];
    }
    
    public function endSession(string $sessionId): ProfilingSession
    {
        $sessionData = $this->sessions[$sessionId];
        
        // XHProf停止
        $xhprofFile = null;
        if (function_exists('xhprof_disable')) {
            $xhprofData = xhprof_disable();
            $filename = sys_get_temp_dir() . "/xhprof_{$sessionId}.xhprof";
            if (file_put_contents($filename, serialize($xhprofData)) !== false) {
                $xhprofFile = $filename;
            }
        }
        
        // Xdebug停止
        $xdebugFile = null;
        if (function_exists('xdebug_stop_trace')) {
            $traceFile = xdebug_stop_trace();
            if (is_string($traceFile) && file_exists($traceFile)) {
                $xdebugFile = $traceFile;
            }
        }
        
        $operations = array_map(
            fn($op) => new ProfilingOperation(
                $op['id'], $op['type'], $op['startTime'], 
                $op['endTime'], $op['metadata']
            ),
            $sessionData['operations']
        );
        
        unset($this->sessions[$sessionId]);
        $this->currentSessionId = null;
        
        return new ProfilingSession(
            $sessionId,
            $operations, 
            $xhprofFile,
            $xdebugFile,
            microtime(true) - $sessionData['startTime']
        );
    }
}
```

#### 2. 各層での使用

**BEAR.Resource層**:
```php
final class CompleteContext extends AbstractContext
{
    public function __construct(
        ResourceObject $resource, 
        OpenContext $openContext,
        ProfilingManagerInterface $profiling,
    ) {
        // プロファイリング操作終了
        $profiling->endOperation($openContext->getOperationId());
        
        // セッション終了（リクエスト完了時）
        $session = $profiling->endSession($openContext->getSessionId());
        
        // セッション情報をContextに統合
        $this->xhprofFile = $session->xhprofFile;
        $this->xdebugTraceFile = $session->xdebugTraceFile;
        $this->profilingOperations = $session->operations;
    }
}
```

**Repository層**:
```php
final class UserRepository
{
    public function __construct(
        private readonly PDO $pdo,
        private readonly ProfilingManagerInterface $profiling,
    ) {}
    
    public function findById(int $id): User
    {
        $operationId = 'db_user_find_' . $id;
        $this->profiling->startOperation($operationId, 'database');
        
        try {
            $stmt = $this->pdo->prepare('SELECT * FROM users WHERE id = ?');
            $stmt->execute([$id]);
            $result = User::fromArray($stmt->fetch());
            
            return $result;
        } finally {
            $this->profiling->endOperation($operationId);
        }
    }
}
```

**HTTP Client層**:
```php
final class ExternalApiClient
{
    public function __construct(
        private readonly HttpClientInterface $httpClient,
        private readonly ProfilingManagerInterface $profiling,
    ) {}
    
    public function fetchData(string $endpoint): array
    {
        $operationId = 'http_' . md5($endpoint);
        $this->profiling->startOperation($operationId, 'http');
        
        try {
            return $this->httpClient->get($endpoint)->toArray();
        } finally {
            $this->profiling->endOperation($operationId);
        }
    }
}
```

## 統合された分析結果

### ProfilingSession の活用
```php
final class CompleteContext extends AbstractContext
{
    public readonly array $profilingOperations;
    
    public function getDbOperations(): array
    {
        return array_filter(
            $this->profilingOperations,
            fn($op) => $op->type === 'database'
        );
    }
    
    public function getHttpOperations(): array
    {
        return array_filter(
            $this->profilingOperations,
            fn($op) => $op->type === 'http'
        );
    }
    
    public function getTotalDbTime(): float
    {
        return array_sum(
            array_map(
                fn($op) => $op->endTime - $op->startTime,
                $this->getDbOperations()
            )
        );
    }
}
```

### セマンティックログでの包括的分析
```json
{
  "type": "bear_resource_complete",
  "uri": "app://self/user-list",
  "code": 200,
  "profilingSession": {
    "sessionId": "req_123",
    "totalDuration": 276.611,
    "operations": [
      {
        "id": "resource_user_list",
        "type": "resource",
        "duration": 12.5
      },
      {
        "id": "db_user_list", 
        "type": "database",
        "duration": 45.2
      },
      {
        "id": "db_user_profile_1",
        "type": "database", 
        "duration": 8.3
      },
      {
        "id": "http_external_api",
        "type": "http",
        "duration": 103.8
      }
    ]
  },
  "xhprofFile": "/tmp/xhprof_req_123.xhprof",
  "xdebugTraceFile": "/tmp/trace_req_123.xt"
}
```

## 利点

### 1. **完全な可視性**
- リクエスト全体のプロファイリング
- 層を跨ぐパフォーマンス分析
- N+1問題の完全な検出

### 2. **柔軟な制御**
- 環境別のプロファイリング有効/無効
- 特定操作のみのプロファイリング
- パフォーマンス影響の最小化

### 3. **包括的分析**
- DB、HTTP、キャッシュ、ファイルI/O の統合分析
- 操作間の依存関係の可視化
- ボトルネック特定の精度向上

## 結論

**DBのような任意の層からのプロファイリングを考慮すると、グローバルプロファイリングマネージャーが最適解**

1. **レイヤー非依存**: どの層からでもプロファイリング可能
2. **包括的分析**: リクエスト全体の統合プロファイリング
3. **柔軟な制御**: 必要な部分のみプロファイリング
4. **既存設計との共存**: Context レベルは結果の受け取りのみ

この設計により、BEAR.Resource固有の処理だけでなく、システム全体のパフォーマンス分析が可能になります。