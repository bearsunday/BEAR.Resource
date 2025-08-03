# プロファイリング処理の設計分析

## 現状の問題

### コード重複の発生
CompleteContextとErrorContextで同じプロファイリング処理が重複：

```php
// CompleteContext.php (66-78行) と ErrorContext.php (53-65行) で同一処理
if (function_exists('xhprof_disable')) {
    $xhprofData = xhprof_disable();
    $filename = sprintf(
        '%s/xhprof_%s_%s.xhprof',
        sys_get_temp_dir(),
        str_replace(['/', ':', '?'], '_', $openContext->uri),
        uniqid('', true),
    );
    
    if (file_put_contents($filename, serialize($xhprofData)) !== false) {
        $this->xhprofFile = $filename;
    }
}
```

## 設計選択肢の比較

### Option 1: AbstractContextにメソッド追加

#### 利点
- **シンプル**: 既存クラス階層を活用
- **即座に適用可能**: 最小限の変更で重複削除
- **テスト容易**: protected メソッドでモック可能

#### 欠点  
- **責務混在**: AbstractContextにインフラ関心事が混入
- **単一責任原則違反**: ドメインロジックとインフラが同居
- **拡張性制限**: 将来的なプロファイラー追加時の柔軟性不足

#### 実装例
```php
abstract class AbstractContext
{
    protected function stopXhprof(string $uri): ?string
    {
        if (!function_exists('xhprof_disable')) {
            return null;
        }
        
        $xhprofData = xhprof_disable();
        $filename = sprintf(
            '%s/xhprof_%s_%s.xhprof',
            sys_get_temp_dir(),
            str_replace(['/', ':', '?'], '_', $uri),
            uniqid('', true),
        );
        
        return file_put_contents($filename, serialize($xhprofData)) !== false ? $filename : null;
    }
    
    protected function stopXdebugTrace(): ?string
    {
        $traceFile = @xdebug_stop_trace();
        return (is_string($traceFile) && file_exists($traceFile)) ? $traceFile : null;
    }
}
```

### Option 2: DIによる分離

#### 利点
- **関心の分離**: プロファイリングロジックを独立したサービスに分離
- **単一責任**: 各クラスの責務が明確
- **テスタビリティ**: モック・スタブが容易
- **拡張性**: 新しいプロファイラーの追加が簡単
- **設定可能**: 環境に応じたプロファイラー切り替え

#### 欠点
- **複雑性**: 新しいクラスとインターフェースが必要
- **DIコンテナ設定**: 依存関係の設定が必要

#### 実装例
```php
interface ProfilerInterface
{
    public function stop(string $uri): ProfileResult;
}

final class ProfileResult
{
    public function __construct(
        public readonly ?string $xhprofFile,
        public readonly ?string $xdebugTraceFile,
    ) {}
}

final class XhprofProfiler implements ProfilerInterface
{
    public function stop(string $uri): ProfileResult
    {
        $xhprofFile = null;
        $xdebugTraceFile = null;
        
        if (function_exists('xhprof_disable')) {
            $xhprofData = xhprof_disable();
            $filename = sprintf(
                '%s/xhprof_%s_%s.xhprof',
                sys_get_temp_dir(),
                str_replace(['/', ':', '?'], '_', $uri),
                uniqid('', true),
            );
            
            if (file_put_contents($filename, serialize($xhprofData)) !== false) {
                $xhprofFile = $filename;
            }
        }
        
        $traceFile = @xdebug_stop_trace();
        if (is_string($traceFile) && file_exists($traceFile)) {
            $xdebugTraceFile = $traceFile;
        }
        
        return new ProfileResult($xhprofFile, $xdebugTraceFile);
    }
}

// CompleteContext使用例
final class CompleteContext extends AbstractContext
{
    public function __construct(
        ResourceObject $resource, 
        OpenContext $openContext,
        private readonly ProfilerInterface $profiler,
    ) {
        // ... 既存処理
        
        $profileResult = $this->profiler->stop($openContext->uri);
        $this->xhprofFile = $profileResult->xhprofFile;
        $this->xdebugTraceFile = $profileResult->xdebugTraceFile;
    }
}
```

## 推奨案: **Option 2 (DI) を採用**

### 理由

#### 1. BEAR.Resourceの設計哲学との整合性
BEAR.Resourceは徹底したDIパターンを採用しており、プロファイリング処理もこの原則に従うべき

#### 2. 将来性と拡張性
```php
// 将来的な拡張例
final class CompositeProfiler implements ProfilerInterface
{
    public function __construct(
        private readonly XhprofProfiler $xhprof,
        private readonly NewRelicProfiler $newRelic,
        private readonly CustomProfiler $custom,
    ) {}
    
    public function stop(string $uri): ProfileResult
    {
        // 複数プロファイラーの統合処理
    }
}
```

#### 3. テスタビリティの向上
```php
final class MockProfiler implements ProfilerInterface
{
    public function stop(string $uri): ProfileResult
    {
        return new ProfileResult('/test/xhprof.data', '/test/trace.xt');
    }
}

// テストでの使用
$context = new CompleteContext($resource, $openContext, new MockProfiler());
```

#### 4. 環境固有の最適化
```php
// 本番環境: パフォーマンス重視
final class ProductionProfiler implements ProfilerInterface
{
    public function stop(string $uri): ProfileResult
    {
        // サンプリング、圧縮、非同期保存
    }
}

// 開発環境: 詳細情報重視  
final class DevelopmentProfiler implements ProfilerInterface
{
    public function stop(string $uri): ProfileResult
    {
        // 全情報記録、詳細トレース
    }
}
```

## 実装手順

### Phase 1: インターフェース定義
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

### Phase 2: 実装クラス作成
```php
final class VerboseProfiler implements ProfilerInterface
{
    public function start(string $uri): void
    {
        if (function_exists('xhprof_enable')) {
            xhprof_enable(XHPROF_FLAGS_CPU + XHPROF_FLAGS_MEMORY);
        }
        
        if (function_exists('xdebug_start_trace')) {
            $traceFile = sys_get_temp_dir() . '/xdebug_' . uniqid() . '.xt';
            xdebug_start_trace($traceFile);
        }
    }
    
    public function stop(string $uri): ProfileResult
    {
        // 現在の実装をここに移動
    }
}
```

### Phase 3: DIコンテナ設定
```php
// Module設定
$this->bind(ProfilerInterface::class)->to(VerboseProfiler::class)->in(Scope::SINGLETON);
```

### Phase 4: Context クラス更新
```php
final class CompleteContext extends AbstractContext
{
    public function __construct(
        ResourceObject $resource, 
        OpenContext $openContext,
        ProfilerInterface $profiler,
    ) {
        // 既存処理
        $profileResult = $profiler->stop($openContext->uri);
        $this->xhprofFile = $profileResult->xhprofFile;
        $this->xdebugTraceFile = $profileResult->xdebugTraceFile;
    }
}
```

## 期待される効果

### 1. 保守性向上
- プロファイリングロジックの一元化
- 重複コードの削除
- 変更時の影響範囲限定

### 2. テスト品質向上
- 単体テストでのモック活用
- プロファイリング処理の独立テスト
- 結合テストでの柔軟な設定

### 3. 運用性向上
- 環境固有の最適化
- プロファイラーの動的切り替え
- パフォーマンス影響の制御

この設計により、BEAR.Resourceの設計原則を保ちながら、保守性と拡張性を両立できます。