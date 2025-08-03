# BEAR.Resource内で完結するプロファイリング重複解決

## 設計原則
- **単一責任**: ロガーライブラリは純粋にログ機能のみ
- **問題の局所化**: 重複コードはBEAR.Resource内で解決
- **依存関係の正常化**: 外部ライブラリに不要なインターフェースを追加しない

## 実装アプローチ

### 1. BEAR.Resource側で独立したProfiler作成

```php
// src/SemanticLog/XhprofProfiler.php
<?php

declare(strict_types=1);

namespace BEAR\Resource\SemanticLog;

final class XhprofProfiler 
{
    public function stop(string $uri): array
    {
        $result = [
            'xhprofFile' => null,
            'xdebugTraceFile' => null,
        ];
        
        // XHProf停止処理
        if (function_exists('xhprof_disable')) {
            $xhprofData = xhprof_disable();
            $filename = sprintf(
                '%s/xhprof_%s_%s.xhprof',
                sys_get_temp_dir(),
                str_replace(['/', ':', '?'], '_', $uri),
                uniqid('', true),
            );
            
            if (file_put_contents($filename, serialize($xhprofData)) !== false) {
                $result['xhprofFile'] = $filename;
            }
        }
        
        // Xdebugトレース停止処理
        $traceFile = @xdebug_stop_trace();
        if (is_string($traceFile) && file_exists($traceFile)) {
            $result['xdebugTraceFile'] = $traceFile;
        }
        
        return $result;
    }
}
```

### 2. AbstractContextに共通メソッド追加

```php
// src/SemanticLog/AbstractContext.php（BEAR.Resource側の基底クラス）
abstract class AbstractContext extends \Koriym\SemanticLogger\AbstractContext
{
    protected function stopProfiling(string $uri): array
    {
        $profiler = new XhprofProfiler();
        return $profiler->stop($uri);
    }
}
```

### 3. Context クラスでの使用

**CompleteContext.php**:
```php
final class CompleteContext extends AbstractContext
{
    public function __construct(
        ResourceObject $resource, 
        OpenContext $openContext
    ) {
        // 既存のドメインロジック
        $this->uri = (string) $resource->uri;
        $this->code = $resource->code;
        $this->headers = $resource->headers;
        $this->body = $resource->body;
        $this->view = $resource->view ?? '';
        
        // プロファイリング処理（共通化）
        $profileResult = $this->stopProfiling($openContext->uri);
        $this->xhprofFile = $profileResult['xhprofFile'];
        $this->xdebugTraceFile = $profileResult['xdebugTraceFile'];
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
    ) {
        $this->exceptionAsString = (string) $exception;
        $this->exceptionId = $exceptionId !== '' ? $exceptionId : $this->createExceptionId();
        
        $this->xhprofFile = null;
        $this->xdebugTraceFile = null;
        
        if ($openContext === null) {
            return;
        }
        
        // プロファイリング処理（共通化）
        $profileResult = $this->stopProfiling($openContext->uri);
        $this->xhprofFile = $profileResult['xhprofFile'];
        $this->xdebugTraceFile = $profileResult['xdebugTraceFile'];
    }
}
```

## 利点

### 1. **責務の純化**
- ロガーライブラリ: 純粋にログ機能のみ
- BEAR.Resource: 自分の関心事（プロファイリング）を自分で解決

### 2. **依存関係の健全性**
- 外部ライブラリに不要なインターフェースを追加しない
- BEAR.Resource内で完結

### 3. **保守性**
- 重複コードの削除
- 変更時の影響範囲がBEAR.Resource内に限定

### 4. **テスタビリティ**
- XhprofProfilerクラスを独立してテスト可能
- AbstractContextのprotectedメソッドをモック可能

## Alternative: DIパターン（BEAR.Resource内完結版）

より柔軟性を求める場合：

```php
// BEAR.Resource内でのインターフェース定義
interface ProfilerInterface
{
    public function stop(string $uri): array;
}

final class VerboseProfiler implements ProfilerInterface
{
    public function stop(string $uri): array
    {
        // 実装
    }
}

final class NullProfiler implements ProfilerInterface
{
    public function stop(string $uri): array
    {
        return ['xhprofFile' => null, 'xdebugTraceFile' => null];
    }
}
```

## 推奨実装

**シンプルな解決**: AbstractContextの共通メソッド
- 最小限の変更
- 明確な責務分離
- 十分な重複削除

この設計により、外部ライブラリに負担をかけずに、BEAR.Resource内の重複問題を適切に解決できます。