# プロファイリング処理のDI化実装プロンプト

## 目的
CompleteContextとErrorContextで重複しているXdebug/XHProfプロファイリング処理をDIパターンで共通化する

## 現状の問題

### 重複コード
CompleteContext.php（66-78行）とErrorContext.php（53-65行）で同一のプロファイリング処理が重複：

```php
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

## 実装要件

### 1. インターフェース定義
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
```

### 2. 実装クラス作成
```php
final class VerboseProfiler implements ProfilerInterface
{
    public function stop(string $uri): ProfileResult
    {
        $xhprofFile = null;
        $xdebugTraceFile = null;
        
        // XHProf停止処理（既存ロジックを移動）
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
        
        // Xdebugトレース停止処理（既存ロジックを移動）
        $traceFile = @xdebug_stop_trace();
        if (is_string($traceFile) && file_exists($traceFile)) {
            $xdebugTraceFile = $traceFile;
        }
        
        return new ProfileResult($xhprofFile, $xdebugTraceFile);
    }
}
```

### 3. Context クラス更新

**CompleteContext.php**:
```php
final class CompleteContext extends AbstractContext
{
    public function __construct(
        ResourceObject $resource, 
        OpenContext $openContext,
        ProfilerInterface $profiler,  // ← DI追加
    ) {
        // 既存のドメインロジック
        $this->uri = (string) $resource->uri;
        $this->code = $resource->code;
        $this->headers = $resource->headers;
        $this->body = $resource->body;
        $this->view = $resource->view ?? '';
        
        // プロファイリング処理（重複削除）
        $profileResult = $profiler->stop($openContext->uri);
        $this->xhprofFile = $profileResult->xhprofFile;
        $this->xdebugTraceFile = $profileResult->xdebugTraceFile;
    }
}
```

**ErrorContext.php**:
```php
final class ErrorContext extends AbstractContext
{
    public function __construct(
        Throwable $exception,
        string $exceptionId = '',
        ?OpenContext $openContext = null,
        ?ProfilerInterface $profiler = null,  // ← DI追加
    ) {
        $this->exceptionAsString = (string) $exception;
        $this->exceptionId = $exceptionId !== '' ? $exceptionId : $this->createExceptionId();
        
        $this->xhprofFile = null;
        $this->xdebugTraceFile = null;
        
        if ($openContext === null || $profiler === null) {
            return;
        }
        
        // プロファイリング処理（重複削除）
        $profileResult = $profiler->stop($openContext->uri);
        $this->xhprofFile = $profileResult->xhprofFile;
        $this->xdebugTraceFile = $profileResult->xdebugTraceFile;
    }
}
```

### 4. static createメソッドの更新

**CompleteContext**:
```php
public static function create(
    ResourceObject $resource, 
    OpenContext $openContext,
    ProfilerInterface $profiler,
): self {
    return new self($resource, $openContext, $profiler);
}
```

**ErrorContext**:
```php
public static function create(
    Throwable $exception,
    string $exceptionId = '',
    ?OpenContext $openContext = null,
    ?ProfilerInterface $profiler = null,
): self {
    return new self($exception, $exceptionId, $openContext, $profiler);
}
```

### 5. DIコンテナ設定
使用箇所でProfilerInterfaceをVerboseProfilerにバインド：

```php
$this->bind(ProfilerInterface::class)->to(VerboseProfiler::class)->in(Scope::SINGLETON);
```

## 削除すべきコード

### CompleteContext.php（66-90行）
```php
// 削除: 重複するプロファイリング処理
if (function_exists('xhprof_disable')) {
    // ... 既存のXHProf処理
}

// 削除: 重複するXdebugトレース処理  
$xdebugId = $openContext->getXdebugId();
if ($xdebugId !== null) {
    // ... 既存のXdebugトレース処理
}
```

### ErrorContext.php（53-79行）
```php
// 削除: 重複するプロファイリング処理
if (function_exists('xhprof_disable')) {
    // ... 既存のXHProf処理
}

// 削除: 重複するXdebugトレース処理
$xdebugId = $openContext->getXdebugId();
if ($xdebugId === null) {
    // ... 既存のXdebugトレース処理
}
```

## 期待される効果

1. **重複削除**: 同一プロファイリング処理の一元化
2. **保守性向上**: プロファイリングロジックの変更が1箇所で済む
3. **テスタビリティ**: ProfilerInterfaceをモックしてテスト可能
4. **責務分離**: ContextクラスはドメインロジックとProfiler結果統合に集中
5. **拡張性**: 将来的な新しいプロファイラー追加が容易

## 注意事項

- 既存のpublicプロパティ（$xhprofFile, $xdebugTraceFile）は維持
- jsonSerialize()メソッドの動作は変更しない
- 後方互換性を保つため、ErrorContextのProfiler注入はオプショナル
- 既存のファイル名生成ロジックは完全に保持

この実装により、プロファイリング処理の重複を削除し、BEAR.Resourceの設計原則に沿ったクリーンなアーキテクチャを実現できます。