# プロファイリング処理の適切なレイヤー分析

## 現状の配置とその問題

### 現在: Contextレベル（利用レベル）に配置
```php
// CompleteContext.php - 利用側に直接配置
final class CompleteContext extends AbstractContext
{
    public function __construct(ResourceObject $resource, OpenContext $openContext)
    {
        // ビジネスロジック
        $this->uri = (string) $resource->uri;
        $this->code = $resource->code;
        
        // プロファイリング処理が混在
        if (function_exists('xhprof_disable')) {
            $xhprofData = xhprof_disable();
            // ファイル保存処理...
        }
    }
}
```

### 問題点
- **責務混在**: ドメインロジック + インフラ処理
- **重複**: CompleteContext、ErrorContext で同じ処理
- **横断的関心事の分散**: ログ、プロファイリングが各所に散在

## レイヤー配置の選択肢

### Option 1: Contextレベル（現状）
```
Application Layer
├── CompleteContext ←── プロファイリング処理
├── ErrorContext   ←── プロファイリング処理  
└── OpenContext    ←── プロファイリング処理
```

**問題**: 横断的関心事が分散、責務混在

### Option 2: ロガーレベル（推奨案）
```
Infrastructure Layer
├── SemanticLogger ←── プロファイリング統合
│   ├── ProfilerInterface
│   ├── XhprofProfiler
│   └── XdebugProfiler
└── Context Layer
    ├── CompleteContext (純粋なドメインロジック)
    ├── ErrorContext   (純粋なドメインロジック)
    └── OpenContext    (純粋なドメインロジック)
```

**利点**: 横断的関心事の集約、責務分離

### Option 3: 中間レイヤー
```
Application Layer
├── ProfilingContextDecorator ←── プロファイリング処理
│   └── delegates to Context
└── Pure Context Classes
```

## 推奨案: **ロガーレベルへの移動**

### 理由 1: 横断的関心事の適切な配置

#### プロファイリングは典型的な横断的関心事
- **ログ記録**: 全ての Context で必要
- **パフォーマンス測定**: システム全体の関心事
- **デバッグ支援**: 開発・運用での共通ニーズ

#### ロガーレベルでの統合処理
```php
final class SemanticLogger implements LoggerInterface
{
    public function __construct(
        private readonly ProfilerInterface $profiler,
        private readonly ContextFactoryInterface $contextFactory,
    ) {}
    
    public function logResourceComplete(ResourceObject $resource, OpenContext $openContext): void
    {
        // プロファイリング停止
        $profileResult = $this->profiler->stop($openContext->uri);
        
        // Context作成（純粋なドメインデータのみ）
        $context = $this->contextFactory->createComplete($resource, $openContext);
        
        // プロファイリング結果を統合
        $enrichedContext = $this->enrichWithProfiling($context, $profileResult);
        
        // ログ出力
        $this->doLog($enrichedContext);
    }
}
```

### 理由 2: Single Responsibility Principle の遵守

#### Context クラスの責務純化
```php
// After: 純粋なドメインロジックのみ
final class CompleteContext extends AbstractContext
{
    public function __construct(
        ResourceObject $resource, 
        OpenContext $openContext
    ) {
        // ドメインデータのみ
        $this->uri = (string) $resource->uri;
        $this->code = $resource->code;
        $this->headers = $resource->headers;
        $this->body = $resource->body;
        $this->view = $resource->view ?? '';
        
        // プロファイリング処理は除去
        // → SemanticLogger が責務を負う
    }
}
```

#### プロファイリングサービスの独立
```php
interface ProfilerInterface
{
    public function start(string $uri): void;
    public function stop(string $uri): ProfileResult;
}

final class VerboseProfiler implements ProfilerInterface
{
    public function stop(string $uri): ProfileResult
    {
        return new ProfileResult(
            xhprofFile: $this->stopXhprof($uri),
            xdebugTraceFile: $this->stopXdebugTrace(),
        );
    }
}
```

### 理由 3: 設定可能性とテスタビリティ

#### 環境別プロファイリング制御
```php
// 本番環境: プロファイリング無効
final class ProductionSemanticLogger extends SemanticLogger
{
    public function __construct()
    {
        parent::__construct(new NullProfiler(), new ContextFactory());
    }
}

// 開発環境: 詳細プロファイリング
final class DevelopmentSemanticLogger extends SemanticLogger  
{
    public function __construct()
    {
        parent::__construct(new VerboseProfiler(), new ContextFactory());
    }
}
```

#### テストでの制御
```php
class SemanticLoggerTest extends TestCase
{
    public function testResourceLogging(): void
    {
        $mockProfiler = $this->createMock(ProfilerInterface::class);
        $mockProfiler->expects($this->once())
                    ->method('stop')
                    ->willReturn(new ProfileResult('/test/prof.xhprof', '/test/trace.xt'));
        
        $logger = new SemanticLogger($mockProfiler, new ContextFactory());
        $logger->logResourceComplete($resource, $openContext);
        
        // プロファイリング結果が正しく統合されているかテスト
    }
}
```

## 具体的な実装アプローチ

### Phase 1: プロファイリング抽象化
```php
interface ProfilerInterface
{
    public function start(string $uri): void;
    public function stop(string $uri): ProfileResult;
}

final class ProfileResult
{
    public function __construct(
        public readonly ?string $xhprofFile,
        public readonly ?string $xdebugTraceFile,
        public readonly array $metadata = [],
    ) {}
}
```

### Phase 2: SemanticLogger への統合
```php
final class SemanticLogger implements LoggerInterface
{
    public function __construct(
        private readonly ProfilerInterface $profiler,
        private readonly ContextFactoryInterface $contextFactory,
        private readonly LoggerInterface $baseLogger,
    ) {}
    
    public function logResourceComplete(ResourceObject $resource, OpenContext $openContext): void
    {
        // 1. プロファイリング停止
        $profileResult = $this->profiler->stop($openContext->uri);
        
        // 2. 純粋なContext作成
        $context = $this->contextFactory->createComplete($resource, $openContext);
        
        // 3. プロファイリング結果を付加
        $context = $this->enrichWithProfiling($context, $profileResult);
        
        // 4. ログ出力
        $this->baseLogger->info('Resource completed', ['context' => $context]);
    }
    
    private function enrichWithProfiling(CompleteContext $context, ProfileResult $result): CompleteContext
    {
        // リフレクション or 新しいContextクラスでプロファイリング情報を付加
        return $context->withProfiling($result);
    }
}
```

### Phase 3: Context クラスの純化
```php
final class CompleteContext extends AbstractContext
{
    // プロファイリング関連プロパティ
    public ?string $xhprofFile = null;
    public ?string $xdebugTraceFile = null;
    
    public function __construct(
        ResourceObject $resource, 
        OpenContext $openContext
    ) {
        // 純粋なドメインロジックのみ
        $this->uri = (string) $resource->uri;
        $this->code = $resource->code;
        $this->headers = $resource->headers;
        $this->body = $resource->body;
        $this->view = $resource->view ?? '';
        
        // プロファイリング処理は削除
    }
    
    public function withProfiling(ProfileResult $result): self
    {
        $instance = clone $this;
        $instance->xhprofFile = $result->xhprofFile;
        $instance->xdebugTraceFile = $result->xdebugTraceFile;
        return $instance;
    }
}
```

## アーキテクチャ上の利点

### 1. 関心の分離
```
ドメインロジック (Context)     ←→ 横断的関心事 (Profiling)
      ↓ 分離                           ↓ 集約
純粋なビジネスロジック              プロファイリングサービス
```

### 2. 拡張性
```php
// 新しいプロファイラーの追加が容易
final class APMProfiler implements ProfilerInterface { ... }
final class CloudWatchProfiler implements ProfilerInterface { ... }

// 複数プロファイラーの組み合わせ
final class CompositeProfiler implements ProfilerInterface
{
    public function __construct(
        private readonly array $profilers
    ) {}
}
```

### 3. 設定柔軟性
```php
// DIコンテナでの設定
$container->bind(ProfilerInterface::class)
          ->to($environment === 'production' ? NullProfiler::class : VerboseProfiler::class);
```

## 結論

**プロファイリング処理はロガーレベルに移動すべき**

### 主な理由:
1. **横断的関心事の適切な配置**: ログ、プロファイリングは全システム共通
2. **責務の純化**: Context は純粋なドメインロジックに集中
3. **テスタビリティ**: プロファイリングを独立してテスト可能
4. **設定可能性**: 環境に応じたプロファイリング制御
5. **保守性**: プロファイリングロジックの一元化

この設計により、**Clean Architecture** の原則を守りながら、実用的で保守しやすいシステムを構築できます。